<?php

/*
 * Pickle
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2015-2015, Pickle community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Pickle\Package\PHP\Command\Release\Windows;

use Closure;
use Exception;
use Pickle\Base\Archive;
use Pickle\Base\Interfaces;
use Pickle\Package\PHP\Util\PackageJson;
use Pickle\Package\Util\Header;

class Binary implements Interfaces\Package\Release
{
    /**
     * @var \Pickle\Base\Interfaces\Package
     */
    protected $pkg;

    /**
     * @var Closure
     */
    protected $cb;

    /**
     * @var bool
     */
    protected $noConvert = false;

    /**
     * @var Interfaces\Package\Build
     */
    protected $build;

    /**
     * Constructor.
     *
     * @param string $path
     * @param Closure $cb
     * @param bool $noConvert
     */
    public function __construct($path, $cb = null, $noConvert = false)
    {
        $this->pkg = $this->readPackage($path);
        $this->cb = $cb;
        $this->noConvert = $noConvert;
    }

    public function __destruct()
    {
        $this->composerJsonBak($this->pkg, true);
    }

    /**
     * Create package.
     */
    public function create(array $args = [])
    {
        if (!isset($args['build']) || !($args['build'] instanceof Interfaces\Package\Build)) {
            throw new Exception('Invalid or NULL object passed as Interfaces\\Package\\Build');
        }
        $this->build = $build = $args['build'];

        $info = $build->getInfo();

        $tmp_dir = $build->getTempDir();

        $tmp = $build->getLog('configure');
        $m = null;
        if (preg_match(',Build dir:\\s+([\\:\\-\\.0-9a-zA-Z\\\\_]+),', $tmp, $m)) {
            if (preg_match(',^[a-z]\\:\\\\,i', $m[1]) && is_dir($m[1])) {
                /* Parsed the fully qualified path */
                $build_dir = $m[1];
            } else {
                /* otherwise construct */
                $build_dir = $tmp_dir . DIRECTORY_SEPARATOR . $m[1];
            }
        } else {
            $build_dir = $info['arch'] == 'x86' ? $tmp_dir : $tmp_dir . DIRECTORY_SEPARATOR . 'x64';
            $build_dir .= DIRECTORY_SEPARATOR . (($info['is_release'] ?? true) ? 'Release' : 'Debug');
            $build_dir .= ($info['thread_safe'] ? '_TS' : '');
        }

        /* Various file paths to pack. */
        $composer_json = $this->pkg->getRootDir() . DIRECTORY_SEPARATOR . 'composer.json';

        if (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'LICENSE')) {
            $license = $tmp_dir . DIRECTORY_SEPARATOR . 'LICENSE';
        } elseif (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'COPYING')) {
            $license = $tmp_dir . DIRECTORY_SEPARATOR . 'COPYING';
        } elseif (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'LICENSE.md')) {
            $license = $tmp_dir . DIRECTORY_SEPARATOR . 'LICENSE.md';
        } elseif (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'COPYING.md')) {
            $license = $tmp_dir . DIRECTORY_SEPARATOR . 'COPYING.md';
        } else {
            throw new Exception("Couldn't find LICENSE");
        }

        $readme = null;
        if (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'README')) {
            $readme = $tmp_dir . DIRECTORY_SEPARATOR . 'README';
        } elseif (file_exists($tmp_dir . DIRECTORY_SEPARATOR . 'README.md')) {
            $readme = $tmp_dir . DIRECTORY_SEPARATOR . 'README.md';
        }

        /* pack the outcome */
        $zip_name = $this->getZipBaseName($build) . '.zip';

        $zipClass = Archive\Factory::getZipperClassName();
        $zip = new $zipClass($zip_name, Interfaces\Archive\Zipper::FLAG_CREATE_OVERWRITE);
        /** @var \Pickle\Base\Interfaces\Archive\Zipper $zip */
        $ext_dll_found = false;
        $ext_names = $this->getMultiExtensionNames();
        foreach ($ext_names as $ext_name) {
            $dll_file = $build_dir . DIRECTORY_SEPARATOR . 'php_' . $ext_name . '.dll';

            if (!file_exists($dll_file)) {
                continue;
            }
            $ext_dll_found = true;
            $zip->addFileWithoutPath($dll_file);

            $pdb_file = $build_dir . DIRECTORY_SEPARATOR . 'php_' . $ext_name . '.pdb';
            if (file_exists($pdb_file)) {
                $zip->addFileWithoutPath($pdb_file);
            }
        }

        if (!$ext_dll_found) {
            throw new Exception("Couldn't find extension DLL");
        }

        $zip->addFileWithoutPath($composer_json);
        $zip->addFileWithoutPath($license);
        if ($readme) {
            $zip->addFileWithoutPath($readme);
        }
    }

    public function packLog(?Interfaces\Package\Build $build = null)
    {
        if (!$build) {
            $build = $this->build;
        }

        $path = $this->getZipBaseName($build) . '-logs.zip';

        $build->packLog($path);

        return realpath($path);
    }

    public function getMultiExtensionNames()
    {
        $info = $this->build->getInfo();
        $ext_names = [$info['name']];

        /* config.w32 can contain multiple EXTENTION definitions, which would lead to
         multiple DLLs be built. */
        $config_w32_path = $this->build->getPackage()->getSourceDir() . DIRECTORY_SEPARATOR . 'config.w32';
        $config_w32 = file_get_contents($config_w32_path);
        $m = null;
        if (preg_match_all("/EXTENSION\\s*\\(\\s*('|\")([a-z0-9_]+)('|\")\\s*,/Sm", $config_w32, $m, PREG_SET_ORDER)) {
            foreach ($m as $r) {
                if (!in_array($r[2], $ext_names)) {
                    $ext_names[] = $r[2];
                }
            }
        }

        return $ext_names;
    }

    protected function composerJsonBak(Interfaces\Package $pkg, $restore = false)
    {
        $composer_json_orig = $pkg->getRootDir() . DIRECTORY_SEPARATOR . 'composer.json';
        $composer_json_bak = $pkg->getRootDir() . DIRECTORY_SEPARATOR . '.composer.json.orig';

        if ($restore) {
            if (file_exists($composer_json_bak)) {
                if (!copy($composer_json_bak, $composer_json_orig)) {
                    throw new Exception('Failed to restore composer.json');
                }
            }
        } else {
            if (file_exists($composer_json_orig)) {
                if (!copy($composer_json_orig, $composer_json_bak)) {
                    throw new Exception('Failed to backup composer.json');
                }
            }
        }
    }

    protected function readPackage($path)
    {
        $package = PackageJson::readPackage($path, $this->noConvert);
        $this->composerJsonBak($package);

        /* For the binary release, json badly need the version informatio
           to show the meta info. If there's ever a binary release support
           for other platforms, this will need to be done, probably. */
        (new Header\Version($package))->updateJSON();

        return $package;
    }

    protected function getZipBaseName(Interfaces\Package\Build $build)
    {
        $info = $build->getInfo();

        return 'php_' . $info['name'] . '-'
            . $info['version'] . '-'
            . $info['php_major'] . '.'
            . $info['php_minor'] . '-'
            . ($info['thread_safe'] ? 'ts' : 'nts') . '-'
            . $info['compiler'] . '-'
            . $info['arch'];
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: fdm=marker */

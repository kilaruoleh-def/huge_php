!function(){var t;t=jQuery,Craft.FindAndReplaceUtility=Garnish.Base.extend({$trigger:null,$form:null,init:function(s){this.$form=t("#"+s),this.$trigger=t("input.submit",this.$form),this.$status=t(".utility-status",this.$form),this.addListener(this.$form,"submit","onSubmit")},onSubmit:function(t){var s=this;t.preventDefault(),this.$trigger.hasClass("disabled")||(this.progressBar?this.progressBar.resetProgressBar():this.progressBar=new Craft.ProgressBar(this.$status),this.progressBar.$progressBar.removeClass("hidden"),this.progressBar.$progressBar.velocity("stop").velocity({opacity:1},{complete:function(){var t=Garnish.getPostData(s.$form),r=Craft.expandPostArray(t),e={params:r};Craft.sendActionRequest("POST",r.action,{data:e}).then((function(t){s.updateProgressBar(),setTimeout(s.onComplete.bind(s),300)})).catch((function(t){var s=t.response;alert(s.data.message)}))}}),this.$allDone&&this.$allDone.css("opacity",0),this.$trigger.addClass("disabled"),this.$trigger.trigger("blur"))},updateProgressBar:function(){this.progressBar.setProgressPercentage(100)},onComplete:function(){var s=this;this.$allDone||(this.$allDone=t('<div class="alldone" data-icon="done" />').appendTo(this.$status),this.$allDone.css("opacity",0)),this.progressBar.$progressBar.velocity({opacity:0},{duration:"fast",complete:function(){s.$allDone.velocity({opacity:1},{duration:"fast"}),s.$trigger.removeClass("disabled"),s.$trigger.trigger("focus")}}),Craft.cp.trackJobProgress(!1,!0)}})}();
//# sourceMappingURL=FindAndReplaceUtility.js.map
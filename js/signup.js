$sa  =jQuery;
$sa(
    function () {
        $sa('.woocommerce-address-fields [name=billing_phone]').on(
            "change", function (e) {
                if(softeria_alerts_mdet.update_otp_enable=='on') {
                    var new_phone = $sa('[name=billing_phone]:last-child').val();
                    var old_phone = $sa('#old_billing_phone').val();
                    if(new_phone != '' && new_phone != old_phone) {
                          $sa(this).parents('form').find('[id^="sa_verify_"]').removeClass("sa-default-btn-hide");
                          $sa('[name="save_address"]').addClass("sa-default-btn-hide");
                    }
                    else{
                        $sa('[name="save_address"]').removeClass("sa-default-btn-hide");
                        $sa(this).parents('form').find('[id^="sa_verify_"]').addClass("sa-default-btn-hide");
                    }
                }
            }
        );
        /* $sa('.sa-default-btn-hide[name="save_address"]').each(function(index) {
        $sa(this).removeClass('sa-default-btn-hide');
        $sa(this).parents('form').find('#sa_verify').addClass("sa-default-btn-hide");
        }); */
        
        $sa('input[id="reg_email"]').each(
            function (index) {
                //if(softeria_alerts_mdet.mail_accept==0)
                {
                     //$sa(this).closest(".form-required").removeClass("form-required").find(".description").remove();
                     //$sa(this).parent().hide();
                }
                /* else if(softeria_alerts_mdet.mail_accept==1){
                $sa(this).parent().children("label").html("Email");
                $sa(this).closest(".form-required").removeClass("form-required").find(".description").remove();
                } */
            }
        );
        var register = $sa("#softeria_alerts_name").closest(".register");
        register.find(".woocommerce-Button, button[name='register']").each(
            function () {
                if ($sa(this).attr("name") == "register") {
                    if (!$sa(this).text()!=softeria_alerts_mdet.signupwithotp) {
                        //$sa(this).val(softeria_alerts_mdet.signupwithotp);
                        //$sa(this).find('span').text(softeria_alerts_mdet.signupwithotp);
                    }
                }
            }
        );
    }
);
// login js
$sa(
    function ($) {
        function isEmpty(el)
        {
            return !$sa.trim(el)
        }
        var tokenCon;
        var akCallback = -1;
        var body = $sa("body");
        var modcontainer = $sa(".softeria-alert-modal");
        var noanim = false;
        /* $.fn.softeria_alerts_login_modal = function($this) {
        show_softeria_alerts_login_modal($this);
        return false
        }; */
        $sa(document).on(
            "click", ".softeria-alert-login-modal", function () {
                //$sa('.softeria-alert-modal').show();
                // if (!$sa(this).attr("attr-disclick")) {
                show_softeria_alerts_login_modal($sa(this))
                // }
                return false
            }
        );
        function getUrlParams(url)
        {
            var params = {};
            url.substring(0).replace(
                /[?&]+([^=&]+)=([^&]*)/gi,
                function (str, key, value) {
                    params[key] = value;
                }
            );
            return params;
        }
    
        function show_softeria_alerts_login_modal($this)
        {
            //$sa(".u-column2").css("display",'none');
            var windowWidth = $sa(window).width();
            var params         = getUrlParams($this.attr("href"));
            var def         = params["default"];
            var showonly     = params["showonly"];
            var modal_id     = params["modal_id"];
        
            $sa("#"+modal_id+".softeria-alert-modal").show();
        
            if (showonly == 'login,register' || showonly == 'register,login') {
        
                if(def == 'login') {
                     $sa("#"+modal_id+" .u-column2").css("display",'none');
                     $sa("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").css("display",'block');
                }
                else{
                    $sa("#"+modal_id+" .backtoLoginContainer, #"+modal_id+" .u-column2").css("display",'block');
                    $sa("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").css("display",'none');
                    //$sa("#"+modal_id+" #slide_form").css("transform","translateX(-373px)");
                }
            }
            else if ((def == 'register' && showonly=='') || showonly=='register') {
                $sa("#"+modal_id+" .u-column1,#"+modal_id+" .signdesc").css("display",'none');
                $sa("#"+modal_id+" .u-column2").css("display",'block');
                //$sa("#slide_form").css("transform","translateX(-373px)");
            }
            else if ((def == 'register' && showonly=='') || showonly=='register_with_otp') {
                $sa("#"+modal_id+" .u-column1,#"+modal_id+" .signdesc").css("display",'none');
                $sa("#"+modal_id+" .u-column2").css("display",'block');
                $sa("#"+modal_id+" .sa_myaccount_btn[name=sa_myaccount_btn_signup]").trigger("click");
                //$sa("#slide_form").css("transform","translateX(-373px)");
            }
            else if ((def == 'login' && showonly=='') || showonly=='login') {
                $sa("#"+modal_id+" .u-column1").css("display",'block');
                $sa("#"+modal_id+" .u-column2,#"+modal_id+" .signdesc").css("display",'none');
            }
        
            var display = $this.attr('data-display');
        
            $sa("#"+modal_id+".softeria-alert-modal.smsproModal").removeClass("from-left from-right");
            $sa("#"+modal_id+".softeria-alert-modal.smsproModal").addClass(display);
        
            if(display == 'from-right') {
                $sa("#"+modal_id+".from-right > .modal-content").animate(
                    {
                        right:'0',
                        opacity:'1',
                        padding: '15px'
                                                                       }, 
                    {
                        easing: 'swing',
                        duration: 200,
                        complete: function () { 
                            var wc_width = $sa("#"+modal_id+" .softeria_alerts_validate_field").width();
                            if($sa("#"+modal_id+" #slide_form .u-column1").length==0) {
                                $sa("#"+modal_id+" #slide_form .woocommerce").css({"width":wc_width});
                            }
                            else
                            {
                                $sa("#"+modal_id+" #slide_form .u-column1, #"+modal_id+" #slide_form .u-column2").css({"width":wc_width});
                            }
                        }
                             }
                );
            }
            if(display == 'from-left') {
                $sa("#"+modal_id+".from-left > .modal-content").animate(
                    {
                        left:'0',
                        opacity:'1',
                        padding: '15px'
                                                                       }, 
                    {
                        easing: 'swing',
                        duration: 200,
                        complete: function () { 
                            if($sa("#"+modal_id+" #slide_form .u-column1").length==0) {
                                var wc_width = $sa("#"+modal_id+" .softeria_alerts_validate_field").width();
                                $sa("#"+modal_id+" #slide_form .woocommerce").css({"width":wc_width});
                            }
                            else
                            {
                                $sa("#"+modal_id+" #slide_form .u-column1, #"+modal_id+" #slide_form .u-column2").css({"width":wc_width});
                            }
                        }
                             }
                );
            }
        
        
        
        
        
        
            /* modcontainer.css({
            display: "block"
            }); */
            return false
        }
    

        $sa(document).on(
            "click", ".softeria-alert-modal .backtoLogin", function () {
                var modal_id = $sa(this).parents(".softeria-alert-modal").attr("id");
                $sa("#"+modal_id+" .backtoLoginContainer").css("display",'none');
                $sa("#"+modal_id+" .signdesc").css("display",'block');
        
                //if($sa("#"+modal_id+".from-left #slide_form").length || $sa("#"+modal_id+".from-right #slide_form").length || $sa("#"+modal_id+".center #slide_form").length){
        
                if($sa("#"+modal_id+" #slide_form").length) {
            
        
                    $sa("#"+modal_id+" #slide_form").css("transform","translateX(0)");
                    $sa("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").show();
                }else{
                    $sa("#"+modal_id+" .u-column2").css("display",'none');
                    $sa("#"+modal_id+" .u-column1").css("display",'block');
                    $sa("#"+modal_id+" .signupbutton").css("display",'block');
                }
            }
        );
    
        $sa(document).on(
            "click", ".softeria-alert-modal .signupbutton", function () {
    
                var modal_id = $sa(this).parents(".softeria-alert-modal").attr("id");
                $sa("#"+modal_id+" .backtoLoginContainer").css("display",'block');
                $sa("#"+modal_id+" .signdesc").css("display",'none');
                //if($sa("#"+modal_id+".from-left #slide_form").length || $sa("#"+modal_id+".from-right #slide_form").length || $sa("#"+modal_id+".center #slide_form").length){
        
                //if($sa("#"+modal_id+" #slide_form").length){
                $sa("#"+modal_id+" .u-column2").show();
                $sa("#"+modal_id+" .u-column1").css("display",'none');
                //$sa("#"+modal_id+" #slide_form").css("transform","translateX(-373px)");
                //}else{
            
                //$sa("#"+modal_id+" .u-column2").css("display",'block');
                //$sa("#"+modal_id+" .u-column1").css("display",'none');
                //}
            }
        );
    }
);

/* $sa(document).on("click", ".softeria-alert-login-modal", function(){
    
    var modal_id = $sa(this).attr('data-modal-id');
    var display = $sa(this).attr('data-display');
    
    $sa(".softeria-alert-modal.smsproModal").removeClass("from-left from-right");
    $sa(".softeria-alert-modal.smsproModal").addClass(display);
    if(display == 'from-right'){
        $sa(".from-right > .modal-content").animate({right:'0',opacity:'1'}, 100);
    }
    if(display == 'from-left'){
        $sa(".from-left > .modal-content").animate({left:'0',opacity:'1'}, 100);;
    }
}); */

$sa(document).on(
    "click",".from-right > .modal-content > .close,.from-left > .modal-content > .close",function () {
        $sa(".modal-content").removeAttr("style");
        $sa(".softeria-alert-modal.smsproModal").hide('slow');
    }
);

$sa('body').click(
    function (e) {
        var container = $sa(".modal-content");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $sa('.softeria-alert-modal > .modal-content > .close').trigger('click');
        }
    }
);
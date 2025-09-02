<?php
/**
 * Template.
 * PHP version 5
 *
 * @category View
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
$admin_message = ( isset($values['admin_message']) ) ? trim($values['admin_message']) : SOFTSMAL_Messages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE');
$visitor_msg = ( isset($values['visitor_message']) ) ? $values['visitor_message'] :SOFTSMAL_Messages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE');
$results = Formidable::getFormFields($values['id']);
$enable_otp = isset($values['softeria_alerts_enable_otp'])?$values['softeria_alerts_enable_otp']:'';
$enable_message = isset($values['softeria_alerts_enable_message'])?$values['softeria_alerts_enable_message']:'';
$admin_number = isset($values['admin_number'])?$values['admin_number']:'';
$visitor_phone = isset($values['visitor_phone'])?$values['visitor_phone']:'';
?>
<div class="frm_grid_container">
<span>
<p class="frm6 frm_form_field">
    <label for="enable_message" class="frm_inline_block">
        <input type="checkbox" name="options[softeria_alerts_enable_message]" id="enable_message" value="1" <?php checked($enable_message, 1); ?> />
        <?php esc_html_e('Enable Message', 'soft-sms-alerts'); ?>
    </label>
    </p>
    <p class="frm6 frm_form_field">
    <label for="enable_otp" class="frm_inline_block">
        <input type="checkbox" name="options[softeria_alerts_enable_otp]" id="enable_otp" value="1" <?php checked($enable_otp, 1); ?> />
        <?php esc_html_e('Enable Mobile Verification', 'soft-sms-alerts'); ?>
    </label>
    </p>
    <p class="frm12 frm_form_field">
    <label for="visitor_phone">
            Select Phone Field        </label>
    <select name="options[visitor_phone]" id="visitor_phone">
    <?php
    if (!empty($results)) {
        foreach ($results as $result) {
            ?>
            <option value="<?php echo $result->id; ?>" <?php echo ($result->id==$visitor_phone)?'selected':''; ?>>
            <?php echo $result->name; ?>
        </option>
            <?php
        }
    }
    ?>
    </select>
</p>
<p>
        <label for="visitor_message">
            Visitor Message        </label>
    </p>
            <div class="tokens">
            <div class="smsprotokens">
    <?php
    if (!empty($results)) {
        foreach ( $results as $form_field ) {
            echo  "<a href='#' data-val='[" . esc_attr($form_field->name.'_'.$form_field->id) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $form_field->name)))."</a> | ";
        }
    }
    ?>
    </div>
    <p class="frm12 frm_form_field">
        <textarea id="visitor_message" name="options[visitor_message]" cols="50" rows="4"><?php echo $visitor_msg; ?>
</textarea>
    </p>
</div>
    <p class="frm12 frm_form_field">
        <label for="admin_number">
            Send Admin SMS To        </label>
        <input type="text" id="admin_number" name="options[admin_number]">
    </p>
    <p>    
    <label for="admin_message">
            Admin Message        </label>
            <div class="tokens">
    <div class="smsprotokens">
    <?php
    if (!empty($results)) {
        foreach ( $results as $form_field ) {
            echo  "<a href='#' data-val='[" . esc_attr($form_field->name.'_'.$form_field->id) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $form_field->name)))."</a> | ";
        }
    }
    ?>
    </div>
    <p class="frm12 frm_form_field">
        <textarea id="admin_message" name="options[admin_message]" cols="50" rows="4"><?php echo $admin_message; ?>
</textarea>
    </p>
</div>
</div>
<script>
var adminnumber = '<?php echo $admin_number; ?>';
var tagInput1     = new TagsInput({
    selector: 'admin_number',
    duplicate : false,
    max : 10,
});
var number = (adminnumber!='') ? adminnumber.split(",") : [];
if(number.length > 0){
    tagInput1.addData(number);
}
jQuery(document).on("click", ".smsprotokens a", function() {
        return insertAtText(jQuery(this).attr("data-val"), jQuery(this).parents(".tokens").find("textarea").attr("id"));
    });
function insertAtText(e, t) {
    var s = document.getElementById(t);
    if (document.all)
        if (s.createTextRange && s.caretPos) {
            var i = s.caretPos;
            i.text = " " == i.text.charAt(i.text.length - 1) ? e + " " : e
        } else s.value = s.value + e;
    else if (s.setSelectionRange) {
        var r = s.selectionStart,
            o = s.selectionEnd,
            n = s.value.substring(0, r),
            l = s.value.substring(o);
        s.value = n + e + l
    } else alert("This version of Mozilla based browser does not support setSelectionRange")
}
</script>
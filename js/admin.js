$sa = jQuery;
function SMSPro_change_nav(e, t) {
  if ($sa(e).attr("tab_type") == "callbacks" && $sa("#default_country_code > option").length < 2) {
    loadCountry();
  }
  $sa(".SMSPro_nav_tabs li").removeClass("SMSPro_active");
  $sa(e).addClass("SMSPro_active");
  $sa(".SMSPro_nav_box").removeClass("SMSPro_active");
  $sa("." + t).addClass("SMSPro_active");
  return false;
}
function disabledMessage() {
  var e = $sa('.SMSPro_box input[type="checkbox"]').length;
  for (var t = 0; e > t; t++) {
    if ($sa('.SMSPro_box input[type="checkbox"]').eq(t).is(":checked") === false) {
      $sa($sa(this).parent().attr("data-href")).find("textarea").attr("readonly", true);
    } else {
      $sa($sa(this).parent().attr("data-href")).find("textarea").removeAttr("readonly");
    }
  }
}
function verifyUser(e) {
  var t = $sa('input[data-id="smspro_name"]').val();
  var s = $sa('input[data-id="smspro_password"]').val();
  var i = $sa(e).text();
  $sa(e).text("Loading...");
  if (t == "" || s == "") {
    $sa("#verify_status").html("<strong>Please Enter Username or Password.</strong>").fadeOut(3e3, function () {
      $sa("#verify_status").html("");
      $sa("#verify_status").removeAttr("style");
    });
    $sa(e).text(i);
  } else {
    $sa.ajax({url: "admin.php", type: "GET", data: "option=smspro-woocommerce-senderlist&nonce=" + smspro.nonce + "&user=" + encodeURIComponent(t) + "&pwd=" + encodeURIComponent(s), crossDomain: true, dataType: "json", contentType: "application/json; charset=utf-8", success: function (t) {
      if (typeof t == "object") {
        var s = t;
      } else {
        var r = t.match("(\\{.*\\})|(\\[.*\\])");
        if (r.length > 0) {
          t = r.shift();
          s = $sa.parseJSON(t);
        } else {
          $sa("#verify_status").html("<strong>Response Error</strong>").fadeOut(3e3, function () {
            $sa("#verify_status").html("");
            $sa("#verify_status").removeAttr("style");
          });
        }
      }
      if (s.status == true) {
        $sa('select[id="smspro_gateway[smspro_api]"]').removeAttr("disabled");
        $sa('select[id="smspro_gateway[smspro_api]"] option').remove();
        if (s.data.length == 0) {
          $sa('select[id="smspro_gateway[smspro_api]"]').html("<option value='SMSPRO'>SMSPRO</option>");
        } else {
          $sa.each(s.data, function (e, t) {
              $sa('select[id="smspro_gateway[smspro_api]"]').append($sa("<option></option>").attr("value", t).text(t));            
          });
        }
        if ($sa('select[id="smspro_gateway[smspro_api]"] option').length == 0 || $sa('select[id="smspro_gateway[smspro_api]"] option').val() == "SELECT") {
          $sa('select[id="smspro_gateway[smspro_api]"]').html("<option value='SMSPRO'>SMSPRO</option>");
        }
      } else {
        $sa('select[id="smspro_gateway[smspro_api]"]').html("<option value='SELECT'>SELECT</option>").attr("disabled", true);
        $sa("#verify_status").html("<strong>" + (s.data !== void 0 ? s.data : s.data) + "</strong>").fadeOut(3e3, function () {
          $sa("#verify_status").html("");
          $sa("#verify_status").removeAttr("style");
        });
      }
      $sa(e).text(i);
      autoSave(e);
    }, error: function (e) {
      console.log(e);
    }});
  }
}
var cnt_contact = 0;
function doSASyncNow(e = null) {
  e.value = "Syncing";
  $sa("#sync_status").css({opacity: "1"});
  var t = $sa("#group_auto_sync").val();
  s = "admin.php";
  $sa.ajax({url: s, type: "GET", data: "option=smspro-group-sync&grp_name=" + t, crossDomain: true, dataType: "json", contentType: "application/json; charset=utf-8", success: function (t) {
    if (t !== null && t.status == "success") {
      if (t.description.cnt_member !== void 0 && t.description.cnt_member > 0) {
        cnt_contact += t.description.cnt_member;
        $sa("#sync_status").text(cnt_contact + " contacts synced");
        $sa("#sa_progressbar").html('<div class="loader"><div class="bar"></div></div>');
        doSASyncNow(e);
      } else {
        $sa("#sync_status").text("sync completed").fadeOut(3e3, function () {
          $sa("#verify_status").html("");
          $sa("#verify_status").removeAttr("style");
        });
        e.value = "Synced";
        $sa("#sa_progressbar").html("");
      }
    } else {
      e.value = "Synced";
      $sa("#sync_status").text("sync completed");
      $sa("#sa_progressbar").html("");
    }
  }, error: function (e) {
    $sa("#sync_status").text("error in syncing groups");
    console.log(e);
  }});
  return false;
}
function logout() {
  $sa.ajax({url: "admin.php", type: "GET", data: "option=smspro-woocommerce-logout&nonce=" + smspro.nonce, crossDomain: true, dataType: "json", contentType: "application/json; charset=utf-8", success: function (e) {
    window.location.reload();
  }, error: function (e) {
    console.log(e);
  }});
}
function selecttemplate(e, t) {
  $sa(t).val(e.value);
  $sa(t).trigger("change");
  return false;
}
function create_group(e) {
  var t = $sa('input[data-id="smspro_name"]').val();
  var s = $sa('input[data-id="smspro_password"]').val();
  $sa(e).text("Please Wait...");
  $sa.ajax({url: "admin.php", type: "GET", data: "option=smspro-woocommerce-creategroup&nonce=" + smspro.nonce + "&user=" + encodeURIComponent(t) + "&pwd=" + encodeURIComponent(s), crossDomain: true, dataType: "json", contentType: "application/json; charset=utf-8", success: function (t) {
    if (typeof t == "object") {
      var s = t;
    } else {
      s = $sa.parseJSON(t);
    }
    if (s.status == "success") {
      $sa('select[id="group_auto_sync"]').removeAttr("disabled");
      $sa('select[id="group_auto_sync"]').html("");
      $sa.each(s.data, function (t, s) {
        $sa('select[id="group_auto_sync"]').append($sa("<option></option>").attr("value", s.name).text(s.name));
        $sa(e).remove();
      });
    }
  }, error: function (e) {
    console.log(e);
  }});
  return false;
}
function insertAtCaret(e, t) {
  var s = document.getElementById(t);
  if (document.all) {
    if (s.createTextRange && s.caretPos) {
      var i = s.caretPos;
      i.text = i.text.charAt(i.text.length - 1) == " " ? e + " " : e;
    } else {
      s.value = s.value + e;
    }
  } else if (s.setSelectionRange) {
    var r = s.selectionStart;
    var o = s.selectionEnd;
    var n = s.value.substring(0, r);
    var l = s.value.substring(o);
    s.value = n + e + l;
  } else {
    alert("This version of Mozilla based browser does not support setSelectionRange");
  }
}
function close_accordion_section() {
  $sa(".cvt-accordion-body-title").removeClass("active");
  $sa(".cvt-accordion .cvt-accordion-body-content").slideUp(300).removeClass("open");
  $sa(".expand_btn").removeClass("active");
}
function openAccordian(e) {
  var t = $sa(e).attr("data-href");
  if ($sa(e).is(".active")) {
    close_accordion_section();
  } else {
    close_accordion_section();
    $sa(e).addClass("active");
    $sa(".cvt-accordion " + t).slideDown(300).addClass("open");
    $sa(".expand_btn", e).addClass("active");
  }
}
function toggleDisabled(e) {
  var t = $sa(e).attr("id");
  if ($sa(e).is(":checked") == 0 || $sa(e).attr("readonly") == "readonly") {
    $sa('[data-parent_id="' + t + '"]').trigger("change").attr("readonly", true).addClass("anchordisabled");
    $sa('[data-parent_id="' + t + '"].chosen-select').attr("disabled", true);
  } else {
    $sa('[data-parent_id="' + t + '"]').removeAttr("disabled").removeAttr("readonly").removeClass("anchordisabled").trigger("change");
  }
  $sa(".chosen-select").trigger("chosen:updated");
}
function moreOptInLabel(e) {
  if (e && $sa(".SMSPro_nav_box").length > 0) {
    $sa(".SMSPro_nav_box .tabset tab-panels section:first-child").show();
    var t = $sa(".SMSPro_nav_box." + e).width();
    $sa(".SMSPro_nav_box." + e + " .tabset ul li").length;
    var s = 0;
    $sa(".SMSPro_nav_box." + e + " .tabset ul li").not(".more_tab").each(function () {
      s += $sa(this).width();
      if (t < s) {
        var i = $sa(".SMSPro_nav_box.SMSPro_active .tabset ul").children(":not(.more_tab)").slice(5);
        $sa(".SMSPro_nav_box." + e + " .more_tab ul").html(i);
        $sa(".SMSPro_nav_box." + e + " .tabset ul li.more_tab").removeClass("hide");
      }
    });
  }
}
$sa(window).on("load", function (e) {
  var t = window.location.hash;
  var s = window.location.hash.substr(1).replace("/", "").split("?")[0];
  if (s != "" && t != "") {
    $sa(".SMSPro_nav_tabs li").removeClass("SMSPro_active");
    $sa(".SMSPro_nav_box").removeClass("SMSPro_active");
    $sa('a[href="' + t + '"]').parent().addClass("SMSPro_active");
    $sa(".SMSPro_box ." + s).addClass("SMSPro_active");
  }
  disabledMessage();
});
$sa('.SMSPro_box input[type="checkbox"]').click(function () {
  if ($sa(this).is(":checked") === false) {
    if ($sa(this).parent().find('input[type="checkbox"]:checked').length == 0) {
      $sa(this).parent().parent().find("textarea").attr("readonly", true);
    }
  } else {
    $sa(this).parent().parent().find("textarea").removeAttr("readonly");
  }
});
$sa("#wc_sms_pro_sms_order_message").on("change keyup input", function () {
  $sa("#wc_sms_pro_sms_order_message_char_count").text($sa(this).val().length);
  if ($sa(this).val().length > 968) {
    $sa("#wc_sms_pro_sms_order_message_char_count").css("color", "red");
  } else {
    $sa("#wc_sms_pro_sms_order_message_char_count").css("color", "green");
  }
});
$sa("a#wc_sms_pro_sms_order_send_message").click(function (e) {
  var t = $sa("div#wc_sms_pro_send_sms_meta_box");
  var s = $sa("textarea#wc_sms_pro_sms_order_message");
  $orderid = $sa("input#wc_sms_pro_order_id");
  if (s.val() == "") {
    t.addClass("processing").block({sms_body: null, timeout: 2e3, message: "Please Enter Your Message.", overlayCSS: {background: "#fff", backgroundSize: "100px 400px", opacity: 1}});
    return false;
  }
  t.addClass("processing").block({sms_body: null, overlayCSS: {background: "#fff", backgroundSize: "100px 400px", opacity: 1}});
  var i = {action: "wc_sms_pro_sms_send_order_sms", sms_body: s.val(), order_id: $orderid.val()};
  $sa.ajax({type: "POST", url: smspro.ajaxurl, data: i, success: function (e) {
    t.removeClass("processing").unblock();
    if (e) {
      var i = JSON.parse(e);
      var r = i.status == "error" ? i.description.desc : "Sent Successfully.";
      t.addClass("smsstatus").block({sms_body: null, timeout: 2e3, message: r, overlayCSS: {background: "#fff", backgroundSize: "100px 400px", opacity: 1}});
      s.val("");
    }
  }, dataType: "html"});
  return false;
});
(function () {
  var e;
  var i;
  var o = {}.hasOwnProperty;
  (i = function () {
    function e() {
      this.options_index = 0;
      this.parsed = [];
    }
    e.prototype.add_node = function (e) {
      if (e.nodeName.toUpperCase() === "OPTGROUP") {
        return this.add_group(e);
      } else {
        return this.add_option(e);
      }
    };
    e.prototype.add_group = function (e) {
      var r;
      var o;
      var t = this.parsed.length;
      this.parsed.push({array_index: t, group: true, label: e.label, title: e.title ? e.title : void 0, children: 0, disabled: e.disabled, classes: e.className});
      var n = [];
      var s = 0;
      for (var i = (o = e.childNodes).length; s < i; s++) {
        r = o[s];
        n.push(this.add_option(r, t, e.disabled));
      }
      return n;
    };
    e.prototype.add_option = function (e, t, s) {
      if (e.nodeName.toUpperCase() === "OPTION") {
        if (e.text === "") {
          this.parsed.push({array_index: this.parsed.length, options_index: this.options_index, empty: true});
        } else {
          if (t != null) {
            this.parsed[t].children += 1;
          }
          this.parsed.push({array_index: this.parsed.length, options_index: this.options_index, value: e.value, text: e.text, html: e.innerHTML, title: e.title ? e.title : void 0, selected: e.selected, disabled: s === true ? s : e.disabled, group_array_index: t, group_label: t != null ? this.parsed[t].label : null, classes: e.className, style: e.style.cssText});
        }
        return this.options_index += 1;
      }
    };
    return e;
  }()).select_to_array = function (e) {
    var t;
    var n;
    var o = new i;
    var s = 0;
    for (var r = (n = e.childNodes).length; s < r; s++) {
      t = n[s];
      o.add_node(t);
    }
    return o.parsed;
  };
  t = function () {
    function e(t, s) {
      this.form_field = t;
      this.options = s != null ? s : {};
      this.label_click_handler = function (e, t) {
        return function () {
          return e.apply(t, arguments);
        };
      }(this.label_click_handler, this);
      if (e.browser_is_supported()) {
        this.is_multiple = this.form_field.multiple;
        this.set_default_text();
        this.set_default_values();
        this.setup();
        this.set_up_html();
        this.register_observers();
        this.on_ready();
      }
    }
    e.prototype.set_default_values = function () {
      this.click_test_action = (e = this, function (t) {
        return e.test_active_click(t);
      });
      this.activate_action = function (e) {
        return function (t) {
          return e.activate_field(t);
        };
      }(this);
      this.active_field = false;
      this.mouse_on_container = false;
      this.results_showing = false;
      this.result_highlighted = null;
      this.is_rtl = this.options.rtl || /\bchosen-rtl\b/.test(this.form_field.className);
      this.allow_single_deselect = this.options.allow_single_deselect != null && this.form_field.options[0] != null && this.form_field.options[0].text === "" && this.options.allow_single_deselect;
      this.disable_search_threshold = this.options.disable_search_threshold || 0;
      this.disable_search = this.options.disable_search || false;
      this.enable_split_word_search = this.options.enable_split_word_search == null || this.options.enable_split_word_search;
      this.group_search = this.options.group_search == null || this.options.group_search;
      this.search_contains = this.options.search_contains || false;
      this.single_backstroke_delete = this.options.single_backstroke_delete == null || this.options.single_backstroke_delete;
      this.max_selected_options = this.options.max_selected_options || Infinity;
      this.inherit_select_classes = this.options.inherit_select_classes || false;
      this.display_selected_options = this.options.display_selected_options == null || this.options.display_selected_options;
      this.display_disabled_options = this.options.display_disabled_options == null || this.options.display_disabled_options;
      this.include_group_label_in_selected = this.options.include_group_label_in_selected || false;
      this.max_shown_results = this.options.max_shown_results || Number.POSITIVE_INFINITY;
      this.case_sensitive_search = this.options.case_sensitive_search || false;
      return this.hide_results_on_select = this.options.hide_results_on_select == null || this.options.hide_results_on_select;
      var e;
    };
    e.prototype.set_default_text = function () {
      if (this.form_field.getAttribute("data-placeholder")) {
        this.default_text = this.form_field.getAttribute("data-placeholder");
      } else if (this.is_multiple) {
        this.default_text = this.options.placeholder_text_multiple || this.options.placeholder_text || e.default_multiple_text;
      } else {
        this.default_text = this.options.placeholder_text_single || this.options.placeholder_text || e.default_single_text;
      }
      this.default_text = this.escape_html(this.default_text);
      return this.results_none_found = this.form_field.getAttribute("data-no_results_text") || this.options.no_results_text || e.default_no_result_text;
    };
    e.prototype.choice_label = function (e) {
      if (this.include_group_label_in_selected && e.group_label != null) {
        return "<b class='group-name'>" + e.group_label + "</b>" + e.html;
      } else {
        return e.html;
      }
    };
    e.prototype.mouse_enter = function () {
      return this.mouse_on_container = true;
    };
    e.prototype.mouse_leave = function () {
      return this.mouse_on_container = false;
    };
    e.prototype.input_focus = function (e) {
      if (this.is_multiple) {
        if (!this.active_field) {
          return setTimeout(function (e) {
            return function () {
              return e.container_mousedown();
            };
          }(this), 50);
        }
      } else if (!this.active_field) {
        return this.activate_field();
      }
    };
    e.prototype.input_blur = function (e) {
      if (!this.mouse_on_container) {
        this.active_field = false;
        return setTimeout(function (e) {
          return function () {
            return e.blur_test();
          };
        }(this), 100);
      }
    };
    e.prototype.label_click_handler = function (e) {
      if (this.is_multiple) {
        return this.container_mousedown(e);
      } else {
        return this.activate_field();
      }
    };
    e.prototype.results_option_build = function (e) {
      var s;
      var i;
      var n;
      var t = "";
      var l = 0;
      var r = 0;
      for (var o = (n = this.results_data).length; r < o && ((i = (s = n[r]).group ? this.result_add_group(s) : this.result_add_option(s)) !== "" && (l++, t += i), (e != null ? e.first : void 0) && (s.selected && this.is_multiple ? this.choice_build(s) : s.selected && !this.is_multiple && this.single_set_selected_text(this.choice_label(s))), !(l >= this.max_shown_results)); r++) {}
      return t;
    };
    e.prototype.result_add_option = function (e) {
      var t;
      var s;
      if (e.search_match && this.include_option_in_results(e)) {
        t = [];
        if (!e.disabled && (!e.selected || !this.is_multiple)) {
          t.push("active-result");
        }
        if (!!e.disabled && (!e.selected || !this.is_multiple)) {
          t.push("disabled-result");
        }
        if (e.selected) {
          t.push("result-selected");
        }
        if (e.group_array_index != null) {
          t.push("group-option");
        }
        if (e.classes !== "") {
          t.push(e.classes);
        }
        (s = document.createElement("li")).className = t.join(" ");
        s.style.cssText = e.style;
        s.setAttribute("data-option-array-index", e.array_index);
        s.innerHTML = e.highlighted_html || e.html;
        if (e.title) {
          s.title = e.title;
        }
        return this.outerHTML(s);
      } else {
        return "";
      }
    };
    e.prototype.result_add_group = function (e) {
      var t;
      var s;
      if ((e.search_match || e.group_match) && e.active_options > 0) {
        (t = []).push("group-result");
        if (e.classes) {
          t.push(e.classes);
        }
        (s = document.createElement("li")).className = t.join(" ");
        s.innerHTML = e.highlighted_html || this.escape_html(e.label);
        if (e.title) {
          s.title = e.title;
        }
        return this.outerHTML(s);
      } else {
        return "";
      }
    };
    e.prototype.results_update_field = function () {
      this.set_default_text();
      if (!this.is_multiple) {
        this.results_reset_cleanup();
      }
      this.result_clear_highlight();
      this.results_build();
      if (this.results_showing) {
        return this.winnow_results();
      }
    };
    e.prototype.reset_single_select_options = function () {
      var s;
      var i;
      var r = [];
      var e = 0;
      for (var t = (s = this.results_data).length; e < t; e++) {
        if ((i = s[e]).selected) {
          r.push(i.selected = false);
        } else {
          r.push(void 0);
        }
      }
      return r;
    };
    e.prototype.results_toggle = function () {
      if (this.results_showing) {
        return this.results_hide();
      } else {
        return this.results_show();
      }
    };
    e.prototype.results_search = function (e) {
      if (this.results_showing) {
        return this.winnow_results();
      } else {
        return this.results_show();
      }
    };
    e.prototype.winnow_results = function () {
      var t;
      var r;
      var o;
      var n;
      var l;
      var h;
      var u;
      var _;
      var d;
      var p;
      this.no_results_clear();
      var c = 0;
      var e = (n = this.get_search_text()).replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
      var a = this.get_search_regex(e);
      var s = 0;
      for (var i = (l = this.results_data).length; s < i; s++) {
        (r = l[s]).search_match = false;
        h = null;
        u = null;
        r.highlighted_html = "";
        if (this.include_option_in_results(r)) {
          if (r.group) {
            r.group_match = false;
            r.active_options = 0;
          }
          if (r.group_array_index != null && this.results_data[r.group_array_index]) {
            if ((h = this.results_data[r.group_array_index]).active_options === 0 && h.search_match) {
              c += 1;
            }
            h.active_options += 1;
          }
          p = r.group ? r.label : r.text;
          if (!r.group || !!this.group_search) {
            u = this.search_string_match(p, a);
            r.search_match = u != null;
            if (r.search_match && !r.group) {
              c += 1;
            }
            if (r.search_match) {
              if (n.length) {
                _ = u.index;
                o = p.slice(0, _);
                t = p.slice(_, _ + n.length);
                d = p.slice(_ + n.length);
                r.highlighted_html = this.escape_html(o) + "<em>" + this.escape_html(t) + "</em>" + this.escape_html(d);
              }
              if (h != null) {
                h.group_match = true;
              }
            } else if (r.group_array_index != null && this.results_data[r.group_array_index].search_match) {
              r.search_match = true;
            }
          }
        }
      }
      this.result_clear_highlight();
      if (c < 1 && n.length) {
        this.update_results_content("");
        return this.no_results(n);
      } else {
        this.update_results_content(this.results_option_build());
        return this.winnow_results_set_highlight();
      }
    };
    e.prototype.get_search_regex = function (e) {
      var s = this.search_contains ? e : "(^|\\s|\\b)" + e + "[^\\s]*";
      if (!this.enable_split_word_search && !this.search_contains) {
        s = "^" + s;
      }
      var t = this.case_sensitive_search ? "" : "i";
      return new RegExp(s, t);
    };
    e.prototype.search_string_match = function (e, t) {
      var s = t.exec(e);
      if (!this.search_contains && (s != null ? s[1] : void 0)) {
        s.index += 1;
      }
      return s;
    };
    e.prototype.choices_count = function () {
      var s;
      if (this.selected_option_count != null) {
        return this.selected_option_count;
      }
      this.selected_option_count = 0;
      var e = 0;
      for (var t = (s = this.form_field.options).length; e < t; e++) {
        if (s[e].selected) {
          this.selected_option_count += 1;
        }
      }
      return this.selected_option_count;
    };
    e.prototype.choices_click = function (e) {
      e.preventDefault();
      this.activate_field();
      if (!this.results_showing && !this.is_disabled) {
        return this.results_show();
      }
    };
    e.prototype.keydown_checker = function (e) {
      var t;
      var s;
      switch (s = (t = e.which) != null ? t : e.keyCode, this.search_field_scale(), s !== 8 && this.pending_backstroke && this.clear_backstroke(), s) {
        case 8:
          this.backstroke_length = this.get_search_field_value().length;
          break;
        case 9:
          if (this.results_showing && !this.is_multiple) {
            this.result_select(e);
          }
          this.mouse_on_container = false;
          break;
        case 13:
        case 27:
          if (this.results_showing) {
            e.preventDefault();
          }
          break;
        case 32:
          if (this.disable_search) {
            e.preventDefault();
          }
          break;
        case 38:
          e.preventDefault();
          this.keyup_arrow();
          break;
        case 40:
          e.preventDefault();
          this.keydown_arrow();
      }
    };
    e.prototype.keyup_checker = function (e) {
      var t;
      var s;
      switch (s = (t = e.which) != null ? t : e.keyCode, this.search_field_scale(), s) {
        case 8:
          if (this.is_multiple && this.backstroke_length < 1 && this.choices_count() > 0) {
            this.keydown_backstroke();
          } else if (!this.pending_backstroke) {
            this.result_clear_highlight();
            this.results_search();
          }
          break;
        case 13:
          e.preventDefault();
          if (this.results_showing) {
            this.result_select(e);
          }
          break;
        case 27:
          if (this.results_showing) {
            this.results_hide();
          }
          break;
        case 9:
        case 16:
        case 17:
        case 18:
        case 38:
        case 40:
        case 91:
          break;
        default:
          this.results_search();
      }
    };
    e.prototype.clipboard_event_checker = function (e) {
      if (!this.is_disabled) {
        return setTimeout(function (e) {
          return function () {
            return e.results_search();
          };
        }(this), 50);
      }
    };
    e.prototype.container_width = function () {
      if (this.options.width == null) {
        return this.form_field.offsetWidth + "px";
      } else {
        return this.options.width;
      }
    };
    e.prototype.include_option_in_results = function (e) {
      return (!this.is_multiple || !!this.display_selected_options || !e.selected) && (!!this.display_disabled_options || !e.disabled) && !e.empty;
    };
    e.prototype.search_results_touchstart = function (e) {
      this.touch_started = true;
      return this.search_results_mouseover(e);
    };
    e.prototype.search_results_touchmove = function (e) {
      this.touch_started = false;
      return this.search_results_mouseout(e);
    };
    e.prototype.search_results_touchend = function (e) {
      if (this.touch_started) {
        return this.search_results_mouseup(e);
      }
    };
    e.prototype.outerHTML = function (e) {
      var t;
      if (e.outerHTML) {
        return e.outerHTML;
      } else {
        (t = document.createElement("div")).appendChild(e);
        return t.innerHTML;
      }
    };
    e.prototype.get_single_html = function () {
      return '<a class="chosen-single chosen-default">\n  <span>' + this.default_text + '</span>\n  <div><b></b></div>\n</a>\n<div class="chosen-drop">\n  <div class="chosen-search">\n    <input class="chosen-search-input" type="text" autocomplete="off" />\n  </div>\n  <ul class="chosen-results"></ul>\n</div>';
    };
    e.prototype.get_multi_html = function () {
      return '<ul class="chosen-choices">\n  <li class="search-field">\n    <input class="chosen-search-input" type="text" autocomplete="off" value="' + this.default_text + '" />\n  </li>\n</ul>\n<div class="chosen-drop">\n  <ul class="chosen-results"></ul>\n</div>';
    };
    e.prototype.get_no_results_html = function (e) {
      return '<li class="no-results">\n  ' + this.results_none_found + " <span>" + this.escape_html(e) + "</span>\n</li>";
    };
    e.browser_is_supported = function () {
      if (window.navigator.appName === "Microsoft Internet Explorer") {
        return document.documentMode >= 8;
      } else {
        return !/iP(od|hone)/i.test(window.navigator.userAgent) && !/IEMobile/i.test(window.navigator.userAgent) && !/Windows Phone/i.test(window.navigator.userAgent) && !/BlackBerry/i.test(window.navigator.userAgent) && !/BB10/i.test(window.navigator.userAgent) && !/Android.*Mobile/i.test(window.navigator.userAgent);
      }
    };
    e.default_multiple_text = "Select Some Options";
    e.default_single_text = "Select an Option";
    e.default_no_result_text = "No results match";
    return e;
  }();
  (e = $sa).fn.extend({chosen: function (i) {
    if (t.browser_is_supported()) {
      return this.each(function (t) {
        var r;
        var o = (r = e(this)).data("chosen");
        if (i === "destroy") {
          if (o instanceof s) {
            o.destroy();
          }
        } else if (!(o instanceof s)) {
          r.data("chosen", new s(this, i));
        }
      });
    } else {
      return this;
    }
  }});
  s = function (s) {
    function r() {
      return r.__super__.constructor.apply(this, arguments);
    }
    (function (e, t) {
      function s() {
        this.constructor = e;
      }
      for (var i in t) {
        if (o.call(t, i)) {
          e[i] = t[i];
        }
      }
      s.prototype = t.prototype;
      e.prototype = new s;
      e.__super__ = t.prototype;
    }(r, t));
    r.prototype.setup = function () {
      this.form_field_jq = e(this.form_field);
      return this.current_selectedIndex = this.form_field.selectedIndex;
    };
    r.prototype.set_up_html = function () {
      var t;
      (t = ["chosen-container"]).push("chosen-container-" + (this.is_multiple ? "multi" : "single"));
      if (this.inherit_select_classes && this.form_field.className) {
        t.push(this.form_field.className);
      }
      if (this.is_rtl) {
        t.push("chosen-rtl");
      }
      var s = {class: t.join(" "), title: this.form_field.title};
      if (this.form_field.id.length) {
        s.id = this.form_field.id.replace(/[^\w]/g, "_") + "_chosen";
      }
      this.container = e("<div />", s);
      this.container.width(this.container_width());
      if (this.is_multiple) {
        this.container.html(this.get_multi_html());
      } else {
        this.container.html(this.get_single_html());
      }
      this.form_field_jq.hide().after(this.container);
      this.dropdown = this.container.find("div.chosen-drop").first();
      this.search_field = this.container.find("input").first();
      this.search_results = this.container.find("ul.chosen-results").first();
      this.search_field_scale();
      this.search_no_results = this.container.find("li.no-results").first();
      if (this.is_multiple) {
        this.search_choices = this.container.find("ul.chosen-choices").first();
        this.search_container = this.container.find("li.search-field").first();
      } else {
        this.search_container = this.container.find("div.chosen-search").first();
        this.selected_item = this.container.find(".chosen-single").first();
      }
      this.results_build();
      this.set_tab_index();
      return this.set_label_behavior();
    };
    r.prototype.on_ready = function () {
      return this.form_field_jq.trigger("chosen:ready", {chosen: this});
    };
    r.prototype.register_observers = function () {
      this.container.on("touchstart.chosen", (e = this, function (t) {
        e.container_mousedown(t);
      }));
      this.container.on("touchend.chosen", function (e) {
        return function (t) {
          e.container_mouseup(t);
        };
      }(this));
      this.container.on("mousedown.chosen", function (e) {
        return function (t) {
          e.container_mousedown(t);
        };
      }(this));
      this.container.on("mouseup.chosen", function (e) {
        return function (t) {
          e.container_mouseup(t);
        };
      }(this));
      this.container.on("mouseenter.chosen", function (e) {
        return function (t) {
          e.mouse_enter(t);
        };
      }(this));
      this.container.on("mouseleave.chosen", function (e) {
        return function (t) {
          e.mouse_leave(t);
        };
      }(this));
      this.search_results.on("mouseup.chosen", function (e) {
        return function (t) {
          e.search_results_mouseup(t);
        };
      }(this));
      this.search_results.on("mouseover.chosen", function (e) {
        return function (t) {
          e.search_results_mouseover(t);
        };
      }(this));
      this.search_results.on("mouseout.chosen", function (e) {
        return function (t) {
          e.search_results_mouseout(t);
        };
      }(this));
      this.search_results.on("mousewheel.chosen DOMMouseScroll.chosen", function (e) {
        return function (t) {
          e.search_results_mousewheel(t);
        };
      }(this));
      this.search_results.on("touchstart.chosen", function (e) {
        return function (t) {
          e.search_results_touchstart(t);
        };
      }(this));
      this.search_results.on("touchmove.chosen", function (e) {
        return function (t) {
          e.search_results_touchmove(t);
        };
      }(this));
      this.search_results.on("touchend.chosen", function (e) {
        return function (t) {
          e.search_results_touchend(t);
        };
      }(this));
      this.form_field_jq.on("chosen:updated.chosen", function (e) {
        return function (t) {
          e.results_update_field(t);
        };
      }(this));
      this.form_field_jq.on("chosen:activate.chosen", function (e) {
        return function (t) {
          e.activate_field(t);
        };
      }(this));
      this.form_field_jq.on("chosen:open.chosen", function (e) {
        return function (t) {
          e.container_mousedown(t);
        };
      }(this));
      this.form_field_jq.on("chosen:close.chosen", function (e) {
        return function (t) {
          e.close_field(t);
        };
      }(this));
      this.search_field.on("blur.chosen", function (e) {
        return function (t) {
          e.input_blur(t);
        };
      }(this));
      this.search_field.on("keyup.chosen", function (e) {
        return function (t) {
          e.keyup_checker(t);
        };
      }(this));
      this.search_field.on("keydown.chosen", function (e) {
        return function (t) {
          e.keydown_checker(t);
        };
      }(this));
      this.search_field.on("focus.chosen", function (e) {
        return function (t) {
          e.input_focus(t);
        };
      }(this));
      this.search_field.on("cut.chosen", function (e) {
        return function (t) {
          e.clipboard_event_checker(t);
        };
      }(this));
      this.search_field.on("paste.chosen", function (e) {
        return function (t) {
          e.clipboard_event_checker(t);
        };
      }(this));
      if (this.is_multiple) {
        return this.search_choices.on("click.chosen", function (e) {
          return function (t) {
            e.choices_click(t);
          };
        }(this));
      } else {
        return this.container.on("click.chosen", function (e) {
          e.preventDefault();
        });
      }
      var e;
    };
    r.prototype.destroy = function () {
      e(this.container[0].ownerDocument).off("click.chosen", this.click_test_action);
      if (this.form_field_label.length > 0) {
        this.form_field_label.off("click.chosen");
      }
      if (this.search_field[0].tabIndex) {
        this.form_field_jq[0].tabIndex = this.search_field[0].tabIndex;
      }
      this.container.remove();
      this.form_field_jq.removeData("chosen");
      return this.form_field_jq.show();
    };
    r.prototype.search_field_disabled = function () {
      this.is_disabled = this.form_field.disabled || this.form_field_jq.parents("fieldset").is(":disabled");
      this.container.toggleClass("chosen-disabled", this.is_disabled);
      this.search_field[0].disabled = this.is_disabled;
      if (!this.is_multiple) {
        this.selected_item.off("focus.chosen", this.activate_field);
      }
      if (this.is_disabled) {
        return this.close_field();
      } else if (this.is_multiple) {
        return;
      } else {
        return this.selected_item.on("focus.chosen", this.activate_field);
      }
    };
    r.prototype.container_mousedown = function (t) {
      var s;
      if (!this.is_disabled) {
        if (!!t && ((s = t.type) === "mousedown" || s === "touchstart") && !this.results_showing) {
          t.preventDefault();
        }
        if (t != null && e(t.target).hasClass("search-choice-close")) {
          return;
        } else {
          if (this.active_field) {
            if (!this.is_multiple && !!t && (e(t.target)[0] === this.selected_item[0] || !!e(t.target).parents("a.chosen-single").length)) {
              t.preventDefault();
              this.results_toggle();
            }
          } else {
            if (this.is_multiple) {
              this.search_field.val("");
            }
            e(this.container[0].ownerDocument).on("click.chosen", this.click_test_action);
            this.results_show();
          }
          return this.activate_field();
        }
      }
    };
    r.prototype.container_mouseup = function (e) {
      if (e.target.nodeName === "ABBR" && !this.is_disabled) {
        return this.results_reset(e);
      }
    };
    r.prototype.search_results_mousewheel = function (e) {
      var t;
      if (e.originalEvent) {
        t = e.originalEvent.deltaY || -e.originalEvent.wheelDelta || e.originalEvent.detail;
      }
      if (t != null) {
        e.preventDefault();
        if (e.type === "DOMMouseScroll") {
          t *= 40;
        }
        return this.search_results.scrollTop(t + this.search_results.scrollTop());
      }
    };
    r.prototype.blur_test = function (e) {
      if (!this.active_field && this.container.hasClass("chosen-container-active")) {
        return this.close_field();
      }
    };
    r.prototype.close_field = function () {
      e(this.container[0].ownerDocument).off("click.chosen", this.click_test_action);
      this.active_field = false;
      this.results_hide();
      this.container.removeClass("chosen-container-active");
      this.clear_backstroke();
      this.show_search_field_default();
      this.search_field_scale();
      return this.search_field.blur();
    };
    r.prototype.activate_field = function () {
      if (!this.is_disabled) {
        this.container.addClass("chosen-container-active");
        this.active_field = true;
        this.search_field.val(this.search_field.val());
        return this.search_field.focus();
      }
    };
    r.prototype.test_active_click = function (t) {
      var s;
      if ((s = e(t.target).closest(".chosen-container")).length && this.container[0] === s[0]) {
        return this.active_field = true;
      } else {
        return this.close_field();
      }
    };
    r.prototype.results_build = function () {
      this.parsing = true;
      this.selected_option_count = null;
      this.results_data = i.select_to_array(this.form_field);
      if (this.is_multiple) {
        this.search_choices.find("li.search-choice").remove();
      } else if (!this.is_multiple) {
        this.single_set_selected_text();
        if (this.disable_search || this.form_field.options.length <= this.disable_search_threshold) {
          this.search_field[0].readOnly = true;
          this.container.addClass("chosen-container-single-nosearch");
        } else {
          this.search_field[0].readOnly = false;
          this.container.removeClass("chosen-container-single-nosearch");
        }
      }
      this.update_results_content(this.results_option_build({first: true}));
      this.search_field_disabled();
      this.show_search_field_default();
      this.search_field_scale();
      return this.parsing = false;
    };
    r.prototype.result_do_highlight = function (e) {
      var t;
      var s;
      var i;
      var r;
      var o;
      if (e.length) {
        this.result_clear_highlight();
        this.result_highlight = e;
        this.result_highlight.addClass("highlighted");
        r = (i = parseInt(this.search_results.css("maxHeight"), 10)) + (o = this.search_results.scrollTop());
        if ((t = (s = this.result_highlight.position().top + this.search_results.scrollTop()) + this.result_highlight.outerHeight()) >= r) {
          return this.search_results.scrollTop(t - i > 0 ? t - i : 0);
        }
        if (s < o) {
          return this.search_results.scrollTop(s);
        }
      }
    };
    r.prototype.result_clear_highlight = function () {
      if (this.result_highlight) {
        this.result_highlight.removeClass("highlighted");
      }
      return this.result_highlight = null;
    };
    r.prototype.results_show = function () {
      if (this.is_multiple && this.max_selected_options <= this.choices_count()) {
        this.form_field_jq.trigger("chosen:maxselected", {chosen: this});
        return false;
      } else {
        this.container.addClass("chosen-with-drop");
        this.results_showing = true;
        this.search_field.focus();
        this.search_field.val(this.get_search_field_value());
        this.winnow_results();
        return this.form_field_jq.trigger("chosen:showing_dropdown", {chosen: this});
      }
    };
    r.prototype.update_results_content = function (e) {
      return this.search_results.html(e);
    };
    r.prototype.results_hide = function () {
      if (this.results_showing) {
        this.result_clear_highlight();
        this.container.removeClass("chosen-with-drop");
        this.form_field_jq.trigger("chosen:hiding_dropdown", {chosen: this});
      }
      return this.results_showing = false;
    };
    r.prototype.set_tab_index = function (e) {
      var t;
      if (this.form_field.tabIndex) {
        t = this.form_field.tabIndex;
        this.form_field.tabIndex = -1;
        return this.search_field[0].tabIndex = t;
      }
    };
    r.prototype.set_label_behavior = function () {
      this.form_field_label = this.form_field_jq.parents("label");
      if (!this.form_field_label.length && this.form_field.id.length) {
        this.form_field_label = e("label[for='" + this.form_field.id + "']");
      }
      if (this.form_field_label.length > 0) {
        return this.form_field_label.on("click.chosen", this.label_click_handler);
      }
    };
    r.prototype.show_search_field_default = function () {
      if (this.is_multiple && this.choices_count() < 1 && !this.active_field) {
        this.search_field.val(this.default_text);
        return this.search_field.addClass("default");
      } else {
        this.search_field.val("");
        return this.search_field.removeClass("default");
      }
    };
    r.prototype.search_results_mouseup = function (t) {
      var s;
      if ((s = e(t.target).hasClass("active-result") ? e(t.target) : e(t.target).parents(".active-result").first()).length) {
        this.result_highlight = s;
        this.result_select(t);
        return this.search_field.focus();
      }
    };
    r.prototype.search_results_mouseover = function (t) {
      var s;
      if (s = e(t.target).hasClass("active-result") ? e(t.target) : e(t.target).parents(".active-result").first()) {
        return this.result_do_highlight(s);
      }
    };
    r.prototype.search_results_mouseout = function (t) {
      if (e(t.target).hasClass("active-result") || e(t.target).parents(".active-result").first()) {
        return this.result_clear_highlight();
      }
    };
    r.prototype.choice_build = function (t) {
      var i;
      var s = e("<li />", {class: "search-choice"}).html("<span>" + this.choice_label(t) + "</span>");
      if (t.disabled) {
        s.addClass("search-choice-disabled");
      } else {
        (i = e("<a />", {class: "search-choice-close", "data-option-array-index": t.array_index})).on("click.chosen", function (e) {
          return function (t) {
            return e.choice_destroy_link_click(t);
          };
        }(this));
        s.append(i);
      }
      return this.search_container.before(s);
    };
    r.prototype.choice_destroy_link_click = function (t) {
      t.preventDefault();
      t.stopPropagation();
      if (!this.is_disabled) {
        return this.choice_destroy(e(t.target));
      }
    };
    r.prototype.choice_destroy = function (e) {
      if (this.result_deselect(e[0].getAttribute("data-option-array-index"))) {
        if (this.active_field) {
          this.search_field.focus();
        } else {
          this.show_search_field_default();
        }
        if (this.is_multiple && this.choices_count() > 0 && this.get_search_field_value().length < 1) {
          this.results_hide();
        }
        e.parents("li").first().remove();
        return this.search_field_scale();
      }
    };
    r.prototype.results_reset = function () {
      this.reset_single_select_options();
      this.form_field.options[0].selected = true;
      this.single_set_selected_text();
      this.show_search_field_default();
      this.results_reset_cleanup();
      this.trigger_form_field_change();
      if (this.active_field) {
        return this.results_hide();
      }
    };
    r.prototype.results_reset_cleanup = function () {
      this.current_selectedIndex = this.form_field.selectedIndex;
      return this.selected_item.find("abbr").remove();
    };
    r.prototype.result_select = function (e) {
      var t;
      var s;
      if (this.result_highlight) {
        t = this.result_highlight;
        this.result_clear_highlight();
        if (this.is_multiple && this.max_selected_options <= this.choices_count()) {
          this.form_field_jq.trigger("chosen:maxselected", {chosen: this});
          return false;
        } else {
          if (this.is_multiple) {
            t.removeClass("active-result");
          } else {
            this.reset_single_select_options();
          }
          t.addClass("result-selected");
          (s = this.results_data[t[0].getAttribute("data-option-array-index")]).selected = true;
          this.form_field.options[s.options_index].selected = true;
          this.selected_option_count = null;
          this.search_field.val("");
          if (this.is_multiple) {
            this.choice_build(s);
          } else {
            this.single_set_selected_text(this.choice_label(s));
          }
          if (this.is_multiple && (!this.hide_results_on_select || e.metaKey || e.ctrlKey)) {
            this.winnow_results();
          } else {
            this.results_hide();
            this.show_search_field_default();
          }
          if (this.is_multiple || this.form_field.selectedIndex !== this.current_selectedIndex) {
            this.trigger_form_field_change({selected: this.form_field.options[s.options_index].value});
          }
          this.current_selectedIndex = this.form_field.selectedIndex;
          e.preventDefault();
          return this.search_field_scale();
        }
      }
    };
    r.prototype.single_set_selected_text = function (e) {
      if (e == null) {
        e = this.default_text;
      }
      if (e === this.default_text) {
        this.selected_item.addClass("chosen-default");
      } else {
        this.single_deselect_control_build();
        this.selected_item.removeClass("chosen-default");
      }
      return this.selected_item.find("span").html(e);
    };
    r.prototype.result_deselect = function (e) {
      var t = this.results_data[e];
      if (this.form_field.options[t.options_index].disabled) {
        return false;
      }
      t.selected = false;
      this.form_field.options[t.options_index].selected = false;
      this.selected_option_count = null;
      this.result_clear_highlight();
      if (this.results_showing) {
        this.winnow_results();
      }
      this.trigger_form_field_change({deselected: this.form_field.options[t.options_index].value});
      this.search_field_scale();
      return true;
    };
    r.prototype.single_deselect_control_build = function () {
      if (this.allow_single_deselect) {
        if (!this.selected_item.find("abbr").length) {
          this.selected_item.find("span").first().after('<abbr class="search-choice-close"></abbr>');
        }
        return this.selected_item.addClass("chosen-single-with-deselect");
      }
    };
    r.prototype.get_search_field_value = function () {
      return this.search_field.val();
    };
    r.prototype.get_search_text = function () {
      return e.trim(this.get_search_field_value());
    };
    r.prototype.escape_html = function (t) {
      return e("<div/>").text(t).html();
    };
    r.prototype.winnow_results_set_highlight = function () {
      var e;
      var t;
      if ((e = (t = this.is_multiple ? [] : this.search_results.find(".result-selected.active-result")).length ? t.first() : this.search_results.find(".active-result").first()) != null) {
        return this.result_do_highlight(e);
      }
    };
    r.prototype.no_results = function (e) {
      var t = this.get_no_results_html(e);
      this.search_results.append(t);
      return this.form_field_jq.trigger("chosen:no_results", {chosen: this});
    };
    r.prototype.no_results_clear = function () {
      return this.search_results.find(".no-results").remove();
    };
    r.prototype.keydown_arrow = function () {
      var e;
      if (this.results_showing && this.result_highlight) {
        if (e = this.result_highlight.nextAll("li.active-result").first()) {
          return this.result_do_highlight(e);
        } else {
          return;
        }
      } else {
        return this.results_show();
      }
    };
    r.prototype.keyup_arrow = function () {
      var e;
      if (this.results_showing || this.is_multiple) {
        if (this.result_highlight) {
          if ((e = this.result_highlight.prevAll("li.active-result")).length) {
            return this.result_do_highlight(e.first());
          } else {
            if (this.choices_count() > 0) {
              this.results_hide();
            }
            return this.result_clear_highlight();
          }
        } else {
          return;
        }
      } else {
        return this.results_show();
      }
    };
    r.prototype.keydown_backstroke = function () {
      var e;
      if (this.pending_backstroke) {
        this.choice_destroy(this.pending_backstroke.find("a").first());
        return this.clear_backstroke();
      } else if ((e = this.search_container.siblings("li.search-choice").last()).length && !e.hasClass("search-choice-disabled")) {
        this.pending_backstroke = e;
        if (this.single_backstroke_delete) {
          return this.keydown_backstroke();
        } else {
          return this.pending_backstroke.addClass("search-choice-focus");
        }
      } else {
        return;
      }
    };
    r.prototype.clear_backstroke = function () {
      if (this.pending_backstroke) {
        this.pending_backstroke.removeClass("search-choice-focus");
      }
      return this.pending_backstroke = null;
    };
    r.prototype.search_field_scale = function () {
      var t;
      var s;
      var i;
      var r;
      var o;
      var n;
      var l;
      if (this.is_multiple) {
        o = {position: "absolute", left: "-1000px", top: "-1000px", display: "none", whiteSpace: "pre"};
        s = 0;
        for (i = (n = ["fontSize", "fontStyle", "fontWeight", "fontFamily", "lineHeight", "textTransform", "letterSpacing"]).length; s < i; s++) {
          o[r = n[s]] = this.search_field.css(r);
        }
        (t = e("<div />").css(o)).text(this.get_search_field_value());
        e("body").append(t);
        l = t.width() + 25;
        t.remove();
        if (this.container.is(":visible")) {
          l = Math.min(this.container.outerWidth() - 10, l);
        }
        return this.search_field.width(l);
      }
    };
    r.prototype.trigger_form_field_change = function (e) {
      this.form_field_jq.trigger("input", e);
      return this.form_field_jq.trigger("change", e);
    };
    return r;
  }();
}.call(this));
$sa(document).on("click", ".smspro_tokens a", function () {
  insertAtCaret($sa(this).attr("data-val"), $sa(this).parents("td").find("textarea").attr("id"));
  return false;
});
$sa(document).on("click", ".cvt-accordion-body-title .notify_box", function (e) {
  e.stopPropagation();
  var t = $sa(this).parent();
  if ($sa(this).prop("checked") && !t.hasClass("active")) {
    openAccordian(t);
  }
});
$sa(document).on("click", ".cvt-accordion-body-title:not(.notify_box)", function (e) {
  openAccordian(this);
  e.preventDefault();
});
$sa('.SMSPro_box input[type="checkbox"],.woocommerce .cvt-accordion input[type="checkbox"]').unbind("click").on("click change", function () {
  toggleDisabled(this);
});
$sa('.SMSPro_box input[type="checkbox"],.woocommerce .cvt-accordion input[type="checkbox"]').each(function (e, t) {
  toggleDisabled(this);
});
$sa("#smspro_form").keydown(function (e) {
  if (e.keyCode == 13 && e.target.nodeName != "TEXTAREA") {
    e.preventDefault();
    return false;
  }
});
$sa("#smspro_reset_settings").on("click", function (e) {
  $sa("#smspro_reset_modal").addClass("sa-show");
  $sa("#smspro_reset_modal").after('<div class="sa-modal-backdrop sa-fade"></div>');
  $sa(".sa-modal-backdrop").addClass("sa-show");
});
$sa(".sa-close, .btn_cancel").on("click", function (e) {
  $sa(".sa-modal").removeClass("sa-show");
  $sa(".sa-modal-backdrop").removeClass("sa-show");
  var t = $sa(".hasError").attr("menu_accord");
  $sa("li[tab_type=" + t + "]").trigger("click");
  window.location.hash = "#" + t;
  var s = $sa(".SMSPro_nav_box").find(".hasErrorField");
  if (!s.parents(".cvt-accordion-body-content").hasClass("open")) {
    var i = s.parents(".cvt-accordion-body-content").attr("id");
    $sa("[data-href=#" + i + "] .expand_btn").trigger("click");
  }
  return s.focus();
});
$sa("#confirmed").on("click", function (e) {
  $sa("#smspro_reset_btn").prop("checked", true);
  $sa("#smspro_form").submit();
  $sa(".sa-modal").removeClass("sa-show");
  $sa(".sa-modal-backdrop").removeClass("sa-show");
});
$sa(document).on("click", ".custom_radio", function (e) {
  var t = ".custom_radio[data-name='" + $sa(this).attr("data-name") + "']";
  if (t !== void 0) {
    $sa(t).attr("checked", false);
    $sa(this).attr("checked", true);
  }
});
$sa(".menu li").click(function () {
  moreOptInLabel($sa(this).attr("tab_type"));
});
$sa(document).ready(function () {
  moreOptInLabel(window.location.hash.replace(/\#/, ""));
});
$sa(document).on("click", ".tabset li input", function () {
  var e = $sa(this).attr("aria-controls");
  $sa(this).closest(".SMSPro_active").find("section.tab-panel").hide();
  if ($sa(this).prop("checked")) {
    $sa(".tab-panels section#" + e).show();
  }
  if ($sa(".more_tab ul").is(":visible")) {
    $sa(".more_tab ul").hide();
  }
});
if (typeof sa_admin_settings != "undefined") {
  $sa(".SMSPro_settings_box textarea").each(function (e, t) {
    var s = $sa(this);
    s.attr("pre_modified_txt", s.text());
  });
  $sa(document).on("click", ".SMSPro_settings_box .reset_text", function () {
    var e = $sa(this).parents(".cvt-accordion-body-content.open");
    var t = $sa(this).parent().parent().find("textarea");
    var s = t.attr("pre_modified_txt");
    t.val(s);
    if (e.length > 0) {
      e.removeClass("sa_bg_warning");
    } else {
      t.parents(".top-border").removeClass("sa_bg_warning");
    }
    $sa(this).parents(".sa_help_guide").remove();
    return false;
  });
  $sa(".SMSPro_settings_box textarea").on("keyup", function () {
    var e = $sa(this);
    if (sa_admin_settings.show_dlt_modal || e.val().match(/\{\#var\#\}/)) {
      var t = $sa(this).parents(".cvt-accordion-body-content.open");
      if (t.length > 0) {
        t.addClass("sa_bg_warning");
      } else {
        e.parents(".top-border").addClass("sa_bg_warning");
      }
      $sa(".SMSPro_active").find(".sa_help_guide").remove();
      var s = e.val().match(/\{\#var\#\}/) ? sa_admin_settings.variable_err : sa_admin_settings.show_dlt_text;
      e.after('<div class="sa_help_guide sa_txt_warning">' + s + "</div>");
      return false;
    }
  });
}

function autoSave(e){var len = $sa('select[id="smspro_gateway[smspro_api]"]:not([disabled]) option').length;if(len==1){$sa(e).text("Loading..."),$sa('#smspro_bckendform_btn').trigger('click');}}

$sa(document).ready(function(){
	$sa('.menu li').on('click', function(){
		var tab_type = $sa(this).attr('tab_type');
		if( tab_type == 'shortcodes' ){
			$sa('.shortcodes').find('.more_tab').addClass('hide');
		}
		});
})



/**@ search*/
const properties = [
	'direction',
	'boxSizing',
	'width',
	'height',
	'overflowX',
	'overflowY',

	'borderTopWidth',
	'borderRightWidth',
	'borderBottomWidth',
	'borderLeftWidth',
	'borderStyle',

	'paddingTop',
	'paddingRight',
	'paddingBottom',
	'paddingLeft',

	'fontStyle',
	'fontVariant',
	'fontWeight',
	'fontStretch',
	'fontSize',
	'fontSizeAdjust',
	'lineHeight',
	'fontFamily',

	'textAlign',
	'textTransform',
	'textIndent',
	'textDecoration',

	'letterSpacing',
	'wordSpacing',

	'tabSize',
	'MozTabSize',
]

const isFirefox = typeof window !== 'undefined' && window['mozInnerScreenX'] != null

/**
 * @param {HTMLTextAreaElement} element
 * @param {number} position
 */
function getCaretCoordinates(element, position) {
	const div = document.createElement('div')
	document.body.appendChild(div)
	
	const style = div.style
	const computed = getComputedStyle(element)

	style.whiteSpace = 'pre-wrap'
	style.wordWrap = 'break-word'
	style.position = 'absolute'
	style.visibility = 'hidden'

	properties.forEach(prop => {
		style[prop] = computed[prop]
	})

	if (isFirefox) {
		if (element.scrollHeight > parseInt(computed.height))
			style.overflowY = 'scroll'
	} else {
		style.overflow = 'hidden'
	}

	div.textContent = element.value.substring(0, position)

	const span = document.createElement('span')
	span.textContent = element.value.substring(position) || '.'
	div.appendChild(span)

	const coordinates = {
		top: span.offsetTop + parseInt(computed['borderTopWidth']),
		left: span.offsetLeft + parseInt(computed['borderLeftWidth']),
		height: parseInt(computed['lineHeight']),
		height: span.offsetHeight
	}

	div.remove()

	return coordinates
}

class Mentionify {
  constructor(ref, menuRef, resolveFn, replaceFn, menuItemFn) {
    this.ref = ref
    this.menuRef = menuRef
    this.resolveFn = resolveFn
    this.replaceFn = replaceFn
    this.menuItemFn = menuItemFn
    this.options = []
    
    this.makeOptions = this.makeOptions.bind(this)
    this.closeMenu = this.closeMenu.bind(this)
    this.selectItem = this.selectItem.bind(this)
    this.onInput = this.onInput.bind(this)
    this.onKeyDown = this.onKeyDown.bind(this)
    this.renderMenu = this.renderMenu.bind(this)
    
    this.ref.addEventListener('input', this.onInput)
    this.ref.addEventListener('keydown', this.onKeyDown)
  }
  
  async makeOptions(query) {
    const options = await this.resolveFn(query)
    if (options.length !== 0) {
      this.options = options
      this.renderMenu()
    } else {
      this.closeMenu()
    }
  }
  
  closeMenu() {
    setTimeout(() => {
      this.options = []
      this.left = undefined
      this.top = undefined
      this.triggerIdx = undefined
      this.renderMenu()
    }, 0)
  }
  
  selectItem(active) {
    return () => {
      const preMention = this.ref.value.substr(0, this.triggerIdx)
      const option = this.options[active]
      const mention = this.replaceFn(option, this.ref.value[this.triggerIdx].replace("@",""))
      const postMention = this.ref.value.substr(this.ref.selectionStart)
      const newValue = `${preMention}${mention}${postMention}`
      this.ref.value = newValue
      const caretPosition = this.ref.value.length - postMention.length
      this.ref.setSelectionRange(caretPosition, caretPosition)
      this.closeMenu()
      this.ref.focus()
    }
  }
  
  onInput(ev) {
    const positionIndex = this.ref.selectionStart
    const textBeforeCaret = this.ref.value.slice(0, positionIndex)
    const tokens = textBeforeCaret.split(/\s/)
    const lastToken = tokens[tokens.length - 1]
    const triggerIdx = textBeforeCaret.endsWith(lastToken)
      ? textBeforeCaret.length - lastToken.length
      : -1
    const maybeTrigger = textBeforeCaret[triggerIdx]
    const keystrokeTriggered = maybeTrigger === '@'
    
    if (!keystrokeTriggered) {
      this.closeMenu()
      return
    }
    
    const query = textBeforeCaret.slice(triggerIdx + 1)
    this.makeOptions(query)
    
    const coords = getCaretCoordinates(this.ref, positionIndex)
    const { top, left } = this.ref.getBoundingClientRect()
	
	var ref_id = $sa(this.ref).closest('.cvt-accordion-body-content.open').attr('id');
	var token_height = $sa("#"+ref_id+" .smspro_tokens").height();
	
    setTimeout(() => {
      this.active 	= 0
      this.left 	= window.scrollX  + coords.left + this.ref.scrollLeft
      //this.top 		= window.scrollY +  coords.top + top + coords.height - this.ref.scrollTop
	  this.top 		=  coords.top + coords.height + token_height + 20 - this.ref.scrollTop
	this.triggerIdx = triggerIdx
      this.renderMenu()
    }, 0)
  }
  
  onKeyDown(ev) {
    let keyCaught = false
    if (this.triggerIdx !== undefined) {
      switch (ev.key) {
        case 'ArrowDown':
          this.active = Math.min(this.active + 1, this.options.length - 1)
          this.renderMenu()
          keyCaught = true
          break
        case 'ArrowUp':
          this.active = Math.max(this.active - 1, 0)
          this.renderMenu()
          keyCaught = true
          break
        case 'Enter':
        case 'Tab':
		  ev.stopImmediatePropagation();
          this.selectItem(this.active)()
          keyCaught = true
          break
      }
    }
    
    if (keyCaught) {
      ev.preventDefault()
    }
  }
  
  renderMenu() {  
    if (this.top === undefined) {
      this.menuRef.hidden = true
      return
    }
    
    this.menuRef.style.left = this.left + 'px'
	this.menuRef.style.top 	= this.top + 'px'
    //this.menuRef.style.top = '155px'
    this.menuRef.innerHTML 	= ''
    
    this.options.forEach((option, idx) => {
      this.menuRef.appendChild(this.menuItemFn(
        option,
        this.selectItem(idx),
        this.active === idx))
    })
    
    this.menuRef.hidden = false
  }
}

var content_json 	= '';

const resolveFn = prefix => prefix === ''
  ? createJSON(content_json)
  : createJSON(content_json).filter(user => user.val.toLocaleLowerCase().startsWith(prefix))

const replaceFn = (user, trigger) => `${trigger}${user.key} `

const menuItemFn = (user, setItem, selected) => {
	const div = document.createElement('div')
	div.setAttribute('role', 'option')
	div.className = 'menu-item'
	if (selected) {
		div.classList.add('selected')
		div.setAttribute('aria-selected', '')
	}
	div.textContent = user.val
	div.onclick = setItem
	return div
}

$sa('.token-area').on("keypress", function(e) {
	if (e.keyCode == 64) {
		var textarea_id = e.target.id;
		var menuarea_id = $sa(this).closest('div').find('.sa-menu-token').attr('id');

		new Mentionify(
			document.getElementById(textarea_id),
			document.getElementById(menuarea_id),
			resolveFn,
			replaceFn,
			menuItemFn
		)
	}
});

function createJSON(e) {
	
		var jsonObj = [];
		
		//for custom sms
		var custom_data = $sa('#custom_token_list').html();
		if(custom_data != '' && typeof custom_data != 'undefined'){
			var parse_custom_data =  JSON.parse(custom_data);	
			jsonObj = parse_custom_data;
		} else {
			$sa(".cvt-accordion-body-content.open .smspro_tokens a, .cvt-accordion-body-content.active .smspro_tokens a, fieldset .smspro_tokens a, .otp-section-token .smspro_tokens a").each(function() {
				if($sa(this).html() != '[...More]'){
					var key = $sa(this).attr("data-val");
					var value = $sa(this).html();
					
					item = {}
					item['key'] = key;
					item['val'] = value;
					jsonObj.push(item);
				} 
			});
		}
		
		var jsonObj = $sa.map(jsonObj, function(value, i) {return {key:value.key, val:value.val}});
		
		var users = jsonObj;
		return users;
}

function custom_sms_token(obj) {
	
	var id = obj;
	if(id != ''){
		$sa.ajax({
			url         : "?option=fetch-order-variable",
			data        : {order_id:id},
			dataType	: 'json',
			success: function(data)
			{
				var arr1	 = data;
				var content1 = parseVariables(arr1);
				$sa('#custom_token_list').html(JSON.stringify(content1) );
			},
			error:function (e,o){
			}
		});
	}

}
var jsonObj2 = [];

function parseVariables(data,prefix='')
{	
	$sa.each(data,function(i,item){
		
		if(typeof item === 'object')
		{
			var nested_key = i.toString().replace(/_/g," ").replace(/orderitem/g,"");
			var key = i.toString().replace(/^_/i,"");

			if(nested_key != ''){
				parseVariables(item,prefix);
			}
		}
		else
		{
			var j 		= i.toString();
			var key 	= i.toString().replace(/_/g," ").replace(/orderitem/g,"");
			var title 	= item;
			var val 	= j.toString().replace(/^_/i,"");
			
			item2 = {}
			item2['key'] = '['+val+']';
			item2['val'] = key.toUpperCase().trim();
			jsonObj2.push(item2);
		}
	});
	
	if(jsonObj2.length > 0){
		var finalobj 	= $sa.map(jsonObj2, function(value, i) {return {key:value.key, val:value.val}});
		var users1 		= finalobj;
		return users1;
	}
}

$sa("body").on('click', function(){
	$sa('.sa-menu-token').attr('hidden','hidden');
});

function loadCountry()
{
	$sa.ajax({
        url: "admin.php",
        type: "GET",
		data: "option=smspro-woocommerce-countrylist&nonce="+smspro.nonce,
        crossDomain: !0,
        dataType: "json",
        success: function(data) {
			if ("object" != typeof data) {
                data = $sa.parseJSON(data);
            }
			var country = '<option value="" data-pattern="'+smspro.pattern+'" selected="selected">Global</option>';
			if(data.status == true)
			{
				var mcountry = '';
				var mcountries = '';
				$sa.each(data.data,function(index,item){
          console.log(item,index);
					country += '<option value="'+item.phone_code+'"';
					if(item.phone_code == smspro.sa_default_countrycode) {
						country += 'selected="selected"';
					}
					country +='>'+item.name+'</option>';
					mcountry += '<option value="'+item.phone_code+'"';
					if($sa.inArray(item.phone_code, smspro.whitelist_countries) != -1) {
						mcountry += 'selected="selected"';
					}
					mcountry +=item.name+'</option>';
					mcountries += '<option value="'+item.phone_code+'"';
					if($sa.inArray(item.phone_code, smspro.allow_otp_countries) != -1) {
						mcountries += 'selected="selected"';
					}
					mcountries +='>'+item.name+'</option>';
				});
			}
			$sa("#whitelist_country").html(mcountry);
			$sa("#allow_otp_country").html(mcountries);
			$sa("#default_country_code").html(country);			
			$sa(".chosen-select").trigger('chosen:updated');
			defaultCountry();
        },
        error: function(e) {
            console.log(e)
        }
    });
}
function defaultCountry()
{
	if(!smspro.islogged)
	{
		try
		{
			$sa.get("https://ipapi.co/json/", function(data, status){
				if(status=='success')
					calling_code = data.country_calling_code.replace(/\+/g,'');
				else{
					calling_code = 91;
				}
				$sa('#default_country_code').val(calling_code);
				$sa('#default_country_code').trigger('change');
			}).fail(function() {
				console.log("ip check url is not working");
				$sa('#default_country_code').val(91);
			});
		}
		catch(e){
			$sa('#default_country_code').val(91);
		}
		$sa('#default_country_code').trigger('change');
	}
}
$sa(window).on('load',function(){
	var url = window.location.href;
	if((url.split('#')[1] == 'callbacks') || !smspro.islogged)
	{
      loadCountry(); 	  
	}	
});
$sa("#create_wf_group").on("click", function (e) {
        create_group(this);
        return false;
});
$sa(".btn_reset_style").on("click", function(e) {
	$sa(this).addClass('sa-reset-initiated'),
	$sa("#smspro_reset_style_modal").addClass("sa-show"), $sa("#smspro_reset_style_modal").after('<div class="sa-modal-backdrop sa-fade"></div>'), $sa(".sa-modal-backdrop").addClass("sa-show"),$sa("#sconfirmed").off().on("click",{post_name: $sa(this).attr("temp-style")}, resetSaStyle)
});
$sa(document).on('click', '#smspro-remind-later', function() {
       $sa.ajax({
        url: "admin.php",
        type: "GET",
		data: "option=dismiss_chatondesk_notice&nonce="+smspro.nonce,
        crossDomain: !0,
        success: function(data) {
			$sa('#smspro-remind-later').parents('.notice-warning').find('.notice-dismiss').trigger('click');
        },
        error: function(e) {
            console.log(e)
        }
    });
});
$sa(document).on('click', '#smspro-sandbox-mode', function() {
       $sa.ajax({
        url: "admin.php",
        type: "GET",
		data: "option=smspro_sandbox_mode&nonce="+smspro.nonce,
        crossDomain: !0,
        success: function(data) {
			window.location.reload();
        },
        error: function(e) {
            console.log(e)
        }
    });
});
function resetSaStyle(event) {
	$sa('.sa-reset-initiated').addClass("anchordisabled");
	$sa(".sa-modal").removeClass("sa-show");
	$sa(".sa-modal-backdrop").removeClass("sa-show"); 
	jQuery.ajax({
			type: "POST",
			url: 'admin.php?postname='+event.data.post_name+'&action=reset_style',                                
			success: function (data) {
					var response = JSON.parse(data);
					if (response.status == "success") {
                   jQuery(".reset_style").html("<strong>Reset Successfully.</strong>").fadeOut(3e3,function(){jQuery(".reset_style").html("")})
                    jQuery(".sa-reset-initiated").removeClass('sa-reset-initiated').hide();					
                    }
					else{
						jQuery(".sa-reset-initiated" ).removeClass("anchordisabled" );
                        jQuery(".sa-reset-initiated" ).css("pointer-events", "cursor" );
					}
                 },                        
    }); 
}
/**@ search ends*/
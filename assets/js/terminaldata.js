//do jquery
jQuery(document).ready(function ($) {
  //set session storage terminal_africa_save_cart_itemcount
  sessionStorage.setItem("terminal_africa_save_cart_itemcount", "0");
  let saveCartTerminalData = () => {
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = $(".Terminal-delivery-logo");
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html.length) {
      //do nothing
      return;
    }
    //Save cart item as parcel
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_save_cart_item",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      success: function (response) {
        // check if response code is 200
        if (response.code != 200) {
          //check if response code is 400
          if (response.code == 400 || response.code == 401) {
            //Swal
            Swal.fire({
              title: "Error!",
              text: response.message,
              icon: "error",
              customClass: {
                title: "swal-title",
                text: "swal-text",
                content: "swal-content",
                confirmButton: "swal-confirm-button",
                cancelButton: "swal-cancel-button"
              },
              type: "error",
              showCancelButton: false,
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              //icon color
              iconColor: "rgb(246 146 32)",
              //swal footer
              footer: `
                <div>
                  <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                </div>
              `
            });
            return;
          }
          //get terminal_africa_save_cart_itemcount
          let terminal_africa_save_cart_itemcount = sessionStorage.getItem(
            "terminal_africa_save_cart_itemcount"
          );
          //check if terminal_africa_save_cart_itemcount is not empty
          if (terminal_africa_save_cart_itemcount != "") {
            //convert to int
            terminal_africa_save_cart_itemcount = parseInt(
              terminal_africa_save_cart_itemcount
            );
            //check if terminal_africa_save_cart_itemcount is less than 3
            if (terminal_africa_save_cart_itemcount < 3) {
              //try again
              saveCartTerminalData();
              //increment terminal_africa_save_cart_itemcount
              terminal_africa_save_cart_itemcount++;
              //save to session
              sessionStorage.setItem(
                "terminal_africa_save_cart_itemcount",
                terminal_africa_save_cart_itemcount
              );
            }
          }
        }
      }
    });
  };

  /**
   * listen to postcode change
   * @return {void}
   */
  let listenToPostcodeChangeTerminal = () => {
    try {
      //get postcode
      let postcode = $("#billing_postcode").val();
      //check if postcode is not empty
      if (postcode != "") {
        //save to session
        window.terminal_billing_postcode = postcode;
      }
    } catch (error) {
      //do nothing
    }
  };

  //check if page url match 'order-received'
  if (window.location.href.indexOf("order-received") > -1) {
    //do nothing
  } else {
    //init
    saveCartTerminalData();
    //set interval
    setInterval(() => {
      //check if #billing_postcode_field display none
      if ($("#billing_postcode_field").css("display") == "none") {
        //check if value is empty
        if ($("#billing_postcode").val() == "") {
          //add value to post code
          $("#billing_postcode").val(window.terminal_billing_postcode);
        }
        //fade in #billing_postcode_field
        $("#billing_postcode_field").show();
      }
      //listen to postcode change
      listenToPostcodeChangeTerminal();
    }, 300);

    //terminal postcode key focus out
    let terminalPostCode = document.getElementById("billing_postcode");
    //check element exist
    if (terminalPostCode) {
      terminalPostCode.addEventListener("focusout", () => {
        var postcode = $("#billing_postcode").val();
        //save to session
        //check if select name terminal_custom_shipping_lga2 exist
        if ($("select[name='terminal_custom_shipping_lga2']").length) {
          //check if postcode is not empty
          if (postcode != "") {
            //check if postcode is not equal to session
            if (sessionStorage.getItem("terminal_postcode") != postcode) {
              //check if select[name='terminal_custom_shipping_lga2 is not empty
              if (
                $("select[name='terminal_custom_shipping_lga2']").val() != ""
              ) {
                //trigger event change
                $("select[name='terminal_custom_shipping_lga2']").trigger(
                  "change"
                );
                sessionStorage.setItem("terminal_postcode", postcode);
              }
            }
          }
        }
      });
    }
  }
});

//terminal phone keyup event
function billing_phone_terminal_focus_out() {
  jQuery(document).ready(function ($) {
    var phone = $("#billing_phone").val();
    //save to session
    //check if select name terminal_custom_shipping_lga2 exist
    if ($("select[name='terminal_custom_shipping_lga2']").length) {
      //check if phone is not empty
      if (phone != "") {
        //check if phone is not equal to session
        if (sessionStorage.getItem("terminal_phone") != phone) {
          //check if select[name='terminal_custom_shipping_lga2 is not empty
          if ($("select[name='terminal_custom_shipping_lga2']").val() != "") {
            //trigger event change
            $("select[name='terminal_custom_shipping_lga2']").trigger("change");
            sessionStorage.setItem("terminal_phone", phone);
          }
        }
      }
    }
  });
}

//reloadCarrierData
let reloadCarrierData = (e) => {
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //check if value is empty $("select[name='terminal_custom_shipping_lga2']")
    if ($("select[name='terminal_custom_shipping_lga2']").val() == "") {
      //Swal
      Swal.fire({
        title: "Error!",
        text: "Please select a shipping city first!",
        icon: "error",
        customClass: {
          title: "swal-title",
          text: "swal-text",
          content: "swal-content",
          confirmButton: "swal-confirm-button",
          cancelButton: "swal-cancel-button"
        },
        type: "error",
        showCancelButton: false,
        confirmButtonColor: "rgb(246 146 32)",
        cancelButtonColor: "rgb(0 0 0)",
        //icon color
        iconColor: "rgb(246 146 32)",
        //swal footer
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      return;
    }
    //trigger event change
    $("select[name='terminal_custom_shipping_lga2']").trigger("change");
  });
};

function debounce(callback, wait) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      callback.apply(this, args);
    }, wait);
  };
}

//terminalSetShippingCrarrier
let terminalSetShippingCrarrier = function (elem, e) {
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //get terminal shipping input
    let terminalimage = $(".Terminal-delivery-logo");
    //get parent element
    let terminal_image_parent = terminalimage.parent();
    //get previous element
    let terminal_image_prev = terminal_image_parent.prev();
    //check if terminal_image_prev is not empty
    if (terminal_image_prev.length) {
      //check if terminal_image_prev is input type radio
      if (terminal_image_prev.is("input[type='radio']")) {
        //check the input
        terminal_image_prev.prop("checked", true);
      }
    }
    let carriername = $(elem).attr("data-carrier-name");
    let amount = $(elem).attr("data-amount");
    let duration = $(elem).attr("data-duration");
    let pickup = $(elem).attr("data-pickup");
    let email = $('input[name="billing_email"]').val();
    let rateid = $(elem).attr("data-rateid");
    let carrierlogo = $(elem).attr("data-image-url");
    //save to session
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_save_shipping_carrier",
        nonce: terminal_africa.nonce,
        carriername: carriername,
        amount: amount,
        duration: duration,
        email: email,
        rateid: rateid,
        pickup: pickup,
        carrierlogo: carrierlogo
      },
      dataType: "json",
      beforeSend: function () {
        // Swal loader
        Swal.fire({
          title: "Please wait...",
          text: "Applying " + carriername,
          imageUrl: terminal_africa.plugin_url + "/img/loader.gif",
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          showConfirmButton: false,
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
      },
      success: function (response) {
        //close swal
        Swal.close();
        //if response code 200
        if (response.code == 200) {
          //save carrier logo to session
          localStorage.setItem("terminal_carrier_logo", carrierlogo);
          //update woocommerce
          $(document.body).trigger("update_checkout");
          //restoreCarriers
          restoreCarriers();
        } else {
          //show error
          Swal.fire({
            title: "Error",
            text: response.message,
            icon: "error",
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: true,
            footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
          });
        }
      },
      error: function (response) {
        //close swal
        Swal.close();
        //show error
        Swal.fire({
          title: "Error",
          text: "Something went wrong, please try again",
          icon: "error",
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          showConfirmButton: true,
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
      }
    });
  });
};

let restoreCarrierData = (e) => {
  jQuery(document).ready(function ($) {
    //check if local storage is not empty
    if (localStorage.getItem("terminal_delivery_html") != null) {
      let terminal_html = localStorage.getItem("terminal_delivery_html");
      //append to terminal_html
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //find parent li
      var terminal_delivery_li = terminal_delivery_html.parent().parent();
      //remove .t-checkout-carriers
      terminal_delivery_li.find(".t-checkout-carriers").remove();
      //append to li
      terminal_delivery_li.append(terminal_html);
    }
  });
};

//overide billing phone
let overideBillingPhone = () => {
  jQuery(document).ready(function ($) {
    let tm_countries = terminal_africa.terminal_africal_countries;
    // console.log(tm_countries);
    //new content
    let new_content = `
    <p class="form-row validate-required" id="billing_phone_field" data-priority="40">
        <label for="billing_phone" class="">Phone&nbsp;<abbr class="required" title="required">*</abbr></label>
        <span style="
        display: flex;
    ">
            <span style="
        width: 31%;
    ">
                <select style="
        height: 100% !important;
    " class="formatCountryTerminal" onchange="formatCountryTerminalchange(this)">
    <option value="">Select</option>
                    ${tm_countries.map((country) => {
                      return `
                        <option value="${country.phonecode}" data-flag="${country.flag}">${country.phonecode} (${country.name})</option>
                    `;
                    })}
                </select>
            </span>
            <span class="woocommerce-input-wrapper" style="
        width: 80%;
    ">
                <input type="number" class="input-text" name="billing_phone_terminal" onkeyup="billing_phone_terminal_key_up(this)" id="billing_phone_terminal" placeholder="Enter phone" onfocuseout="testnow()">
                <input type="hidden" class="input-text" name="billing_phone" id="billing_phone" placeholder="Phone">
            </span>
        </span>
    </p>
    `;
    //get billing phone
    let billing_phone = $("#billing_phone_field");
    //check if billing phone exist
    if (billing_phone.length > 0) {
      //replace with new content
      billing_phone.replaceWith(new_content);
    }

    //select2 template
    let formatCountryTerminal = (country) => {
      if (!country.id) {
        return country.text;
      }
      var $country = country.element.dataset.flag + " " + country.text;
      return $country;
    };

    //select2 .formatCountryTerminal
    $(".formatCountryTerminal").select2({
      placeholder: "Country Code",
      allowClear: true,
      //template
      templateResult: formatCountryTerminal,
      templateSelection: formatCountryTerminal,
      //clear default
      //height
      height: "100% !important",
      //width
      width: "100% !important"
    });
  });
};

//clear session billing_phone_terminal
sessionStorage.removeItem("billing_phone_terminal");
//set interval #billing_phone_terminal
setInterval(() => {
  jQuery(document).ready(function ($) {
    //get session
    let session = sessionStorage.getItem("billing_phone_terminal");
    //check if session is empty
    if (session == null) {
      //check if the element exist #billing_phone_terminal
      if ($("#billing_phone_terminal").length > 0) {
        // console.log("focus");
        //add on focusout
        $("#billing_phone_terminal").on("focusout", function () {
          // console.log("focus out");
          //focus out #billing_phone_terminal
          billing_phone_terminal_focus_out();
        });
        //set session
        sessionStorage.setItem("billing_phone_terminal", "true");
      }
    }
  });
}, 1000);

//on change .formatCountryTerminal
function formatCountryTerminalchange(elem) {
  jQuery(document).ready(function ($) {
    let phonecode = $(this).val();
    //check if phonecode not include +
    if (!phonecode.includes("+")) {
      phonecode = "+" + phonecode;
    }
    //remove - and space
    phonecode = phonecode.replace(/[- ]/g, "");
    //get billing phone
    let billing_phone = $("#billing_phone_terminal");
    //final phone
    let final_phone = "";
    //check if billing phone exist
    if (billing_phone.length > 0) {
      //get phone
      let phone = billing_phone.val();
      //check if phone is not empty
      if (phone != "") {
        //final phone
        final_phone = phonecode + phone;
        //set final phone
        $("#billing_phone").val(final_phone);
      }
    }
  });
}

//on keyup #billing_phone_terminal
function billing_phone_terminal_key_up() {
  jQuery(document).ready(function ($) {
    let phonecode = $(".formatCountryTerminal").val();
    //check if phonecode not include +
    if (!phonecode.includes("+")) {
      phonecode = "+" + phonecode;
    }
    //remove - and space
    phonecode = phonecode.replace(/[- ]/g, "");
    //get billing phone
    let billing_phone = $("#billing_phone_terminal");
    //final phone
    let final_phone = "";
    //check if billing phone exist
    if (billing_phone.length > 0) {
      //get phone
      let phone = billing_phone.val();
      //check if phone is not empty
      if (phone != "") {
        //final phone
        final_phone = phonecode + phone;
        // console.log(final_phone);
        //set final phone
        $("#billing_phone").val(final_phone);
      }
    }
  });
}

// overideBillingPhone();

//set interval carrier logo
setInterval(function () {
  jQuery(document).ready(function ($) {
    //check if local storage is not empty
    if (localStorage.getItem("terminal_carrier_logo") != null) {
      let terminal_carrier_logo = localStorage.getItem("terminal_carrier_logo");
      //set carrier logo
      let img = $(".Terminal-delivery-logo");
      //check if parent has element .woocommerce-Price-amount amount
      if (img.parent().find(".woocommerce-Price-amount").length) {
        img.attr("src", terminal_carrier_logo);
      }
    } else {
      let old_url = `${terminal_africa.plugin_url}/img/logo-footer.png`;
      let img = $(".Terminal-delivery-logo");
      img.attr("src", old_url);
    }

    //use each to loop through all select2-container
    $(".select2-container").each(function () {
      //check if width is not 100%
      if ($(this).css("width") != "100%") {
        //check if width is is 142.109px or 508.854px ignore
        if (
          $(this).css("width") == "142.109px" ||
          $(this).css("width") == "508.854px"
        ) {
          return;
        }
        //css
        $(this).css({
          width: "100% !important"
        });
      }
    });
    //label for billing_postcode
    let labelTerminal = $("label[for='billing_postcode']");
    //replace with new label
    labelTerminal.replaceWith(
      `<label for="billing_postcode">Postcode / ZIP <abbr class="required" title="required">*</abbr></label>`
    );
    //localstorage data
    restoreCarriers();
  });
}, 1000);

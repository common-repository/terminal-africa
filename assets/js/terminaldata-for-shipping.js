//do jquery
jQuery(document).ready(function ($) {
  //check if page url match 'order-received'
  if (window.location.href.indexOf("order-received") > -1) {
    //do nothing
  } else {
    //set interval
    setInterval(() => {
      //check if #shipping_postcode_field display none
      if ($("#shipping_postcode_field").css("display") == "none") {
        //add value to post code
        $("#shipping_postcode").val(terminal_shipping_postcode);
        //fade in #shipping_postcode_field
        $("#shipping_postcode_field").show();
      }
    }, 300);

    //terminal postcode key focus out
    let terminalPostCode = document.getElementById("shipping_postcode");
    //check if element exists
    if (terminalPostCode) {
      terminalPostCode.addEventListener("focusout", () => {
        var postcode = $("#shipping_postcode").val();
        //save to session
        //check if select name terminal_custom_shipping_lga2_terminalShipping exist
        if (
          $("select[name='terminal_custom_shipping_lga2_terminalShipping']")
            .length
        ) {
          //check if postcode is not empty
          if (postcode != "") {
            //check if postcode is not equal to session
            if (
              sessionStorage.getItem("terminal_postcode_shipping") != postcode
            ) {
              //check if select[name='terminal_custom_shipping_lga2_terminalShipping is not empty
              if (
                $(
                  "select[name='terminal_custom_shipping_lga2_terminalShipping']"
                ).val() != ""
              ) {
                //trigger event change
                $(
                  "select[name='terminal_custom_shipping_lga2_terminalShipping']"
                ).trigger("change");
                sessionStorage.setItem("terminal_postcode_shipping", postcode);
              }
            }
          }
        }
      });
    }
  }
});

//terminal phone keyup event
function shipping_phone_terminal_focus_out() {
  jQuery(document).ready(function ($) {
    var phone = $("#shipping_phone").val();
    //save to session
    //check if select name terminal_custom_shipping_lga2_terminalShipping exist
    if (
      $("select[name='terminal_custom_shipping_lga2_terminalShipping']").length
    ) {
      //check if phone is not empty
      if (phone != "") {
        //check if phone is not equal to session
        if (sessionStorage.getItem("terminal_phone_shipping") != phone) {
          //check if select[name='terminal_custom_shipping_lga2_terminalShipping is not empty
          if (
            $(
              "select[name='terminal_custom_shipping_lga2_terminalShipping']"
            ).val() != ""
          ) {
            //trigger event change
            $(
              "select[name='terminal_custom_shipping_lga2_terminalShipping']"
            ).trigger("change");
            sessionStorage.setItem("terminal_phone_shipping", phone);
          }
        }
      }
    }
  });
}

//reloadCarrierData_terminalShipping
let reloadCarrierData_terminalShipping = (e) => {
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //check if value is empty $("select[name='terminal_custom_shipping_lga2_terminalShipping']")
    if (
      $(
        "select[name='terminal_custom_shipping_lga2_terminalShipping']"
      ).val() == ""
    ) {
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
    $("select[name='terminal_custom_shipping_lga2_terminalShipping']").trigger(
      "change"
    );
  });
};

//overide billing phone
let overideBillingPhoneShipping = () => {
  jQuery(document).ready(function ($) {
    let tm_countries = terminal_africa.terminal_africal_countries;
    // console.log(tm_countries);
    //new content
    let new_content = `
    <p class="form-row validate-required" id="shipping_phone_field" data-priority="40">
        <label for="shipping_phone" class="">Phone&nbsp;<abbr class="required" title="required">*</abbr></label>
        <span style="
        display: flex;
    ">
            <span style="
        width: 31%;
    ">
                <select style="
        height: 100% !important;
    " class="formatCountryTerminal" onchange="formatCountryTerminalchangeShipping(this)">
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
                <input type="number" class="input-text" name="shipping_phone_terminal" onkeyup="shipping_phone_terminal_key_up(this)" id="shipping_phone_terminal" placeholder="Enter phone" onfocuseout="testnow()">
                <input type="hidden" class="input-text" name="shipping_phone" id="shipping_phone" placeholder="Phone">
            </span>
        </span>
    </p>
    `;
    //get billing phone
    let shipping_phone = $("#shipping_phone_field");
    //check if billing phone exist
    if (shipping_phone.length > 0) {
      //replace with new content
      shipping_phone.replaceWith(new_content);
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

//clear session shipping_phone_terminal
sessionStorage.removeItem("shipping_phone_terminal");
//set interval #shipping_phone_terminal
setInterval(() => {
  jQuery(document).ready(function ($) {
    //get session
    let session = sessionStorage.getItem("shipping_phone_terminal");
    //check if session is empty
    if (session == null) {
      //check if the element exist #shipping_phone_terminal
      if ($("#shipping_phone_terminal").length > 0) {
        // console.log("focus");
        //add on focusout
        $("#shipping_phone_terminal").on("focusout", function () {
          // console.log("focus out");
          //focus out #shipping_phone_terminal
          shipping_phone_terminal_focus_out();
        });
        //set session
        sessionStorage.setItem("shipping_phone_terminal", "true");
      }
    }
  });
}, 1000);

//on change .formatCountryTerminal
function formatCountryTerminalchangeShipping(elem) {
  jQuery(document).ready(function ($) {
    let phonecode = $(this).val();
    //check if phonecode not include +
    if (!phonecode.includes("+")) {
      phonecode = "+" + phonecode;
    }
    //remove - and space
    phonecode = phonecode.replace(/[- ]/g, "");
    //get billing phone
    let shipping_phone = $("#shipping_phone_terminal");
    //final phone
    let final_phone = "";
    //check if billing phone exist
    if (shipping_phone.length > 0) {
      //get phone
      let phone = shipping_phone.val();
      //check if phone is not empty
      if (phone != "") {
        //final phone
        final_phone = phonecode + phone;
        //set final phone
        $("#shipping_phone").val(final_phone);
      }
    }
  });
}

//on keyup #shipping_phone_terminal
function shipping_phone_terminal_key_up() {
  jQuery(document).ready(function ($) {
    let phonecode = $(".formatCountryTerminal").val();
    //check if phonecode not include +
    if (!phonecode.includes("+")) {
      phonecode = "+" + phonecode;
    }
    //remove - and space
    phonecode = phonecode.replace(/[- ]/g, "");
    //get billing phone
    let shipping_phone = $("#shipping_phone_terminal");
    //final phone
    let final_phone = "";
    //check if billing phone exist
    if (shipping_phone.length > 0) {
      //get phone
      let phone = shipping_phone.val();
      //check if phone is not empty
      if (phone != "") {
        //final phone
        final_phone = phonecode + phone;
        // console.log(final_phone);
        //set final phone
        $("#shipping_phone").val(final_phone);
      }
    }
  });
}

// overideBillingPhoneShipping();

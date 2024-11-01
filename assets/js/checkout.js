////////////////////////////// Terminal Africa Checkout ////////////////////////////
/// This is for the core woocommerce plugin and the native checkout page and woocommerce checkout plugin 'Cartflow'.
////////////////////////////////////////////////////////////

//global variables
window.terminal_objects = {
  woocommerce_block_checkout: false
};

/**
 * WooCommerce Block Element Checkout
 * Check if woocommerce checkout block element is enabled
 *
 */
let woocommerceBlockElementCheckout = () => {
  try {
    //check if checkout page has .wp-block-woocommerce-checkout
    if (jQuery(".wp-block-woocommerce-checkout").length > 0) {
      //update terminal object
      terminal_objects.woocommerce_block_checkout = true;
      //block .wp-block-woocommerce-checkout with message
      setTimeout(() => {
        jQuery(".wp-block-woocommerce-checkout").before(`
        <div class="alignwide">
           <div class="woocommerce-error" role="alert">
              <strong>Terminal Africa:</strong> Please switch to classic checkout for Terminal Africa to work.
              
              ${terminal_africa.edit_checkout_page_link}
            </div>
        </div>
        `);
      }, 1000);
    } else {
      //update terminal object
      terminal_objects.woocommerce_block_checkout = false;
    }
  } catch (error) {}
};

//init on page load
woocommerceBlockElementCheckout();

/**
 * updateCoreWoocommerceElements
 * @param {*} state
 * @param {*} finaltext
 */
let updateCoreWoocommerceElements = (state = "", finaltext = "") => {
  jQuery(document).ready(function ($) {
    //find select[name='billing_state'] option with value and set it to selected
    $('select[name="billing_state"]')
      .find("option")
      .each(function (index, element) {
        if ($(element).val() == state) {
          let element2 = document.querySelector('select[name="billing_state"]');
          element2.value = state;
          element2.dispatchEvent(new Event("change"));
        } else {
          $(element).removeAttr("selected");
        }
      });
    //get selected option
    var selected_option = $('select[name="billing_state"]')
      .find("option:selected")
      .val();
    document.querySelector("#billing_city").value = finaltext;
    //form name="checkout" input name billing_city
    //custom
    document.querySelector(
      'form[name="checkout"] input[name="billing_city"]'
    ).value = finaltext;
    //state
    if (
      document.querySelector(
        'form[name="checkout"] input[name="billing_state"]'
      )
    ) {
      document.querySelector(
        'form[name="checkout"] input[name="billing_state"]'
      ).value = selected_option;
    }
  });
};

/**
 * terminalsetValue2
 * @param {*} elem
 */
function terminalsetValue2(elem) {
  jQuery(document).ready(function ($) {
    //fade in .terminal-woo-checkout-get-rate
    $(".terminal-woo-checkout-get-rate").each(function () {
      $(this).fadeIn();
    });
    //get lga
    var lga = $(elem).val();
    var stateText = $('select[name="terminal_custom_shipping_state2"]')
      .find("option:selected")
      .text();
    //check if billing_country exist
    if ($('select[name="billing_country"]').length > 0) {
      var countryCode = $('select[name="billing_country"]').val();
    } else {
      var countryCode = $('input[name="billing_country"]').val();
    }
    var state = $('select[name="terminal_custom_shipping_state2"]').val();
    var finaltext = lga + ", " + stateText;

    //process the terminal rates
    var email = $('input[name="billing_email"]').val();
    var first_name = $('input[name="billing_first_name"]').val();
    var last_name = $('input[name="billing_last_name"]').val();
    var phone = $('input[name="billing_phone"]').val();
    let tm_countries = terminal_africa.terminal_africal_countries;
    //find country where isoCode is NG
    let tm_country = tm_countries.find(
      (country) => country.isoCode === countryCode
    );
    //phone code
    let phonecode = tm_country.phonecode;
    //check if phonecode not include +
    if (!phonecode.includes("+")) {
      phonecode = "+" + phonecode;
    }
    //remove - and space
    // phonecode = phonecode.replace(/[- ]/g, "");
    //remove + and space and special characters form phone
    // phone = phone.replace(/[-+()]/g, "");
    if (phone) {
      if (!phone.includes("+")) {
        //check if phone has +
        //append to phone
        phone = phonecode + phone;
      }
    } else {
      //append to phone
      phone = "";
    }

    var line_1 = $('input[name="billing_address_1"]').val();
    var billing_postcode = $('input[name="billing_postcode"]').val();
    //process updateCoreWoocommerceElements
    updateCoreWoocommerceElements(state, finaltext);
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = $(".Terminal-delivery-logo");
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html.length) {
      //do nothing
      return;
    }
    //update global variables
    window.terminal_billing_postcode = billing_postcode;
    //terminal_shipping_postcode
    window.terminal_shipping_postcode = billing_postcode;
    //terminal_shipping_state
    window.terminal_shipping_state = state;
    //terminal_shipping_city
    window.terminal_shipping_city = lga;
    //terminal_billing_city
    window.terminal_billing_city = lga;
    //terminal_billing_state
    window.terminal_billing_state = state;
    //reset carrier data
    termianlDataParcel.clearCarrierData();
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_process_terminal_rates",
        nonce: terminal_africa.nonce,
        state: stateText,
        stateCode: state,
        countryCode: countryCode,
        city: lga,
        email: email,
        first_name: first_name,
        last_name: last_name,
        phone: phone,
        line_1: line_1,
        billing_postcode: billing_postcode
      },
      dataType: "json",
      beforeSend: function () {
        //update woocommerce
        $(document.body).trigger("update_checkout");
        // Swal loader
        Swal.fire({
          title: "Please wait...",
          text: "Getting Shipping Rates",
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
        //Swal close
        Swal.close();
        //check response is 200
        if (response.code === 200) {
          //do something cool
          //clear .t-checkout-carriers
          $(".t-checkout-carriers").remove();
          let terminal_html = `
          <div class="t-checkout-carriers">
          `;
          //loop through response.data
          $.each(response.data, function (indexInArray, value) {
            /////////// APPLY TERMINAL SHIPMENT INSURANCE FEE IF ENABLED /////////
            value.amount = value.amount + value.metadata.insurance_fee;
            value.default_amount =
              value.default_amount + value.metadata.insurance_default_fee;
            ////////// APPLY TERMINAL SHIPMENT INSURANCE FEE IF ENABLED /////////
            //overwrite value.amount
            let terminalAfricaPriceMarkUpPercentage =
              response.terminal_price_markup;
            //check if not empty
            if (terminalAfricaPriceMarkUpPercentage) {
              //parse to int
              terminalAfricaPriceMarkUpPercentage = parseInt(
                terminalAfricaPriceMarkUpPercentage
              );
              //apply percentage
              value.amount =
                value.amount +
                (value.amount * terminalAfricaPriceMarkUpPercentage) / 100;

              //do same to default_amount
              if (value.default_amount) {
                value.default_amount =
                  value.default_amount +
                  (value.default_amount * terminalAfricaPriceMarkUpPercentage) /
                    100;
              }
            }
            //////Display handler ///////
            let amount_for_handler = terminalFormatCurrency(value.amount);
            //process the amount
            let amount = new Intl.NumberFormat("en-US", {
              style: "currency",
              currency: terminal_africa.currency
              //  currencyDisplay: "narrowSymbol",
              //remove decimal
              //  minimumFractionDigits: 0
            }).format(amount_for_handler);
            //set default amount
            let default_amount = value.amount;
            //check if value.default_amount exist
            if (value.default_amount) {
              //set amount to default_amount
              default_amount = value.default_amount;

              //////Display handler ///////
              let default_amount_for_handler =
                terminalFormatCurrency(default_amount);

              //set amount to currency
              amount = new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: terminal_africa.currency
                //  currencyDisplay: "narrowSymbol",
                //remove decimal
                //  minimumFractionDigits: 0
              }).format(default_amount_for_handler);
            }
            //append to terminal_html
            terminal_html += `
                <div class="t-checkout-single" onclick="terminalSetShippingCrarrier(this, event)" data-carrier-name="${
                  value.carrier_name
                }" data-amount="${default_amount}" data-duration="${
                  value.delivery_time
                }" data-pickup="${value.pickup_time}" data-rateid="${
                  value.rate_id
                }" data-image-url="${value.carrier_logo}">
                <label for="shipping">
                <div style="display: flex;justify-content: start;align-items: center;    padding: 10px;">
                  <img class="Terminal-carrier-delivery-logo" alt="${
                    value.carrier_name
                  }" title="${
                    value.carrier_name
                  }" style="width: auto;height: auto;margin-right: 10px;    max-width: 30px;" src="${
                    value.carrier_logo
                  }">
                  <p style=""> 
                        <span style="font-weight: bolder;">${
                          value.carrier_name
                        }</span> ${"- " + amount}  ${
                          terminal_africa_parcel.terminal_user_carrier_shipment_timeline !=
                          "true"
                            ? ""
                            : "- " + value.delivery_time
                        }
                    </p>
                </div>
                </label>
                </div>
            `;
          });
          //close div
          terminal_html += `
          </div>
          `;
          //append to terminal_html
          var terminal_delivery_html = $(".Terminal-delivery-logo");
          //check if terminal_delivery_html exist
          if (!terminal_delivery_html.length) {
            //do nothing
            return;
          }
          //check if terminal_html exist is more than one
          if (terminal_delivery_html.length > 1) {
            //loop through terminal_delivery_html
            $.each(terminal_delivery_html, function (indexInArray, value) {
              //find parent li
              var terminal_delivery_li = $(value).parent().parent();
              //save terminal_html to localstorage
              localStorage.setItem("terminal_delivery_html", terminal_html);
              //append to li
              terminal_delivery_li.append(terminal_html);
            });
          } else {
            //find parent li
            var terminal_delivery_li = terminal_delivery_html.parent().parent();
            //save terminal_html to localstorage
            localStorage.setItem("terminal_delivery_html", terminal_html);
            //append to li
            terminal_delivery_li.append(terminal_html);
          }
        } else {
          //swal error
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: response.message,
            footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
          });
        }
      },
      error: function (xhr, status, error) {
        //swal error
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong!: " + xhr.responseText,
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
      }
    });
    //end
  });
}

/**
 * wooSelectElementOptions
 *
 * get Woocommerce state select
 *
 * @param {*} $
 */
let wooSelectElementOptions = ($) => {
  //data option
  var data_options = {
    state: [],
    city: [
      {
        state: "",
        lga: "",
        placeholder: "Select City"
      }
    ]
  };
  var wc_state_options = $("select[name='billing_state']").find("option");
  wc_state_options.each(function (index, element) {
    var state_value = $(element).val();
    var state_name = $(element).text();

    // var state_lga = state_value.split(", ")[0];
    //if state_name is undefined skip
    if (state_name === undefined) {
      return;
    }
    //push to data_options
    data_options.state.push({
      state: state_name,
      value: state_value
    });
    // data_options.city.push({
    //   state: state_name,
    //     lga: state_lga
    // });
  });
  //array unique
  var unique_state = [...new Set(data_options.state)];
  var state_options = "";
  $.each(unique_state, function (indexInArray, valueOfElement) {
    state_options += `<option value="${valueOfElement.value}" ${
      valueOfElement.value == terminal_billing_state ? "selected" : ""
    }>${valueOfElement.state}</option>`;
  });
  //return
  return {
    state_options,
    data_options
  };
};

/////// EVENT //////////////////////////////////

/**
 * do_terminal_calculation
 * @param {*} datas
 * @param {*} selected
 */
let do_terminal_calculation = (datas, selected = "") => {
  jQuery(document).ready(function ($) {
    //check data count
    if (datas.length < 1) {
      datas = [
        {
          name: "Select City",
          value: ""
        }
      ];
    }
    var lga = "<option value=''>Select City</option>";
    //create options
    $.each(datas, function (indexInArray, valueOfElement) {
      lga += `<option value="${valueOfElement.name}"  ${
        selected == valueOfElement.name ? "selected" : ""
      }
      >${valueOfElement.name}</option>`;
    });
    //check if terminal_custom_shipping_lga2 element exists
    if (!$("#terminal_custom_shipping_lga2").length) {
      $("#terminal_custom_shipping_state2").after(`
        <p class="form-row address-field validate-required validate-state form-row-wide woocommerce-validated" id="terminal_custom_shipping_lga2" >
          <label for="terminal_custom_shipping_lga2">City <abbr class="required" title="required">*</abbr></label>
          <span class="woocommerce-input-wrapper">
            <select name="terminal_custom_shipping_lga2" class="lga_select" style="    width: 100% !important;" onchange="terminalsetValue2(this)">
                ${lga}
            </select>
          </span>
        </p>
      `);
      //check if select2 is added to select[name="terminal_custom_shipping_lga2"]
      if (
        !$("select[name='terminal_custom_shipping_lga2']").hasClass(
          "select2-hidden-accessible"
        )
      ) {
        //select2 init
        $('select[name="terminal_custom_shipping_lga2"]').select2({
          placeholder: "Select City",
          // allowClear: true,
          width: "100%"
        });
      } else {
        //destroy and update
        // $('select[name="terminal_custom_shipping_lga2"]').select2("destroy");
        $('select[name="terminal_custom_shipping_lga2"]').select2({
          placeholder: "Select City",
          // allowClear: true,
          width: "100%"
        });
      }
    } else {
      //destroy and update
      // $('select[name="terminal_custom_shipping_lga2"]').select2("destroy");
      //update select
      $('select[name="terminal_custom_shipping_lga2"]').html(lga);
      //update select2
      $('select[name="terminal_custom_shipping_lga2"]').select2({
        placeholder: "Select City",
        // allowClear: true,
        width: "100%"
      });
    }

    //recalculate
    $(document.body).trigger("update_checkout");
  });
};

/**
 * terminalButton
 *
 * overide submit button
 */
let terminalButton = () => {
  jQuery(document).ready(function ($) {
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = $(".Terminal-delivery-logo");
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html.length) {
      //do nothing
      return;
    }

    //check if billing_country exist
    if ($('select[name="billing_country"]').length > 0) {
      var countrycode = $('select[name="billing_country"]').val();
    } else {
      var countrycode = $('input[name="billing_country"]').val();
    }
    let submitButton = $("button[name='woocommerce_checkout_place_order']");
    // console.log(submitButton);
    submitButton.removeAttr("id");
    //remove event on button
    submitButton.off("click");
    //change type to button
    submitButton.attr("type", "button");
    //if input is checked
    submitButton.click(function (e) {
      e.preventDefault();
      var form = $(this).parents("form");
      var state = $('select[name="terminal_custom_shipping_state2"]').val();
      var lga = $('select[name="terminal_custom_shipping_lga2"]').val();
      //if countrycode is empty
      if (countrycode == "") {
        //show error
        Swal.fire({
          icon: "error",
          title: "Please select a country",
          text: "Country is required",
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
          //footer
          footer: `
                <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                </div>
                `
        });
        return;
      }
      //check if countrycode is not NG
      // if (countrycode == "NG") {
      //if state is empty
      if (
        state == "" ||
        state == null ||
        state == undefined ||
        state == "null" ||
        state == "undefined"
      ) {
        //show error
        Swal.fire({
          icon: "error",
          title: "Please select a state",
          text: "State is required",
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
          //footer
          footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
        });
        return;
      }
      //if lga is empty
      if (
        lga == "" ||
        lga == null ||
        lga == undefined ||
        lga == "null" ||
        lga == "undefined"
      ) {
        //show error
        Swal.fire({
          icon: "error",
          title: "Please select a city",
          text: "City is required",
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
          //footer
          footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
        });
        return;
      }
      // }
      //check if shipment is applied
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html.length) {
        //do nothing
        return;
      }

      //find parent li for the first index
      var terminal_delivery_li = terminal_delivery_html.parents("li");

      //check if input is checked
      if (terminal_delivery_li.find("input").is(":checked")) {
        var parent_method_id = terminal_delivery_li.find("input").val();

        //check if parent_method_id is not matching terminal_delivery_li
        if (/terminal/.test(parent_method_id)) {
          //check if class exist woocommerce-Price-amount
          if (!terminal_delivery_li.find(".woocommerce-Price-amount").length) {
            //show error
            Swal.fire({
              icon: "error",
              title: "Please select a carrier",
              text: "Please choose your delivery option to complete your order",
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              //footer
              footer: `
                <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                </div>
                `
            });
            //return
            return;
          }
        }

        //check if #payment_method_terminal_africa_payment exist and is checked
        if (
          $("#payment_method_terminal_africa_payment").length &&
          $("#payment_method_terminal_africa_payment").is(":checked")
        ) {
          //check if class exist woocommerce-Price-amount
          if (!terminal_delivery_li.find(".woocommerce-Price-amount").length) {
            //show error
            Swal.fire({
              icon: "error",
              title: "Please select a carrier",
              text: "Please choose your delivery option to complete your order",
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              //footer
              footer: `
                <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                </div>
                `
            });
            //return
            return;
          }
        }
      }
      //if all is good
      //submit form
      form.submit();
      //clear local storage
      localStorage.removeItem("terminal_delivery_html");
    });
  });
};

/**
 * restoreCarriers
 */
let restoreCarriers = () => {
  jQuery(document).ready(function ($) {
    //check if local storage is not empty
    if (localStorage.getItem("terminal_delivery_html") != null) {
      //check if t-restore does not exist
      if (!$(".t-checkout-carriers").length) {
        let terminal_html = localStorage.getItem("terminal_delivery_html");
        //append to terminal_html
        var terminal_delivery_html = $(".Terminal-delivery-logo");
        //check if terminal_delivery_html exist
        if (!terminal_delivery_html.length) {
          //do nothing
          return;
        }
        //check if terminal_html exist is more than one
        if (terminal_delivery_html.length > 1) {
          //loop through terminal_delivery_html
          $.each(terminal_delivery_html, function (indexInArray, value) {
            //find parent li
            var terminal_delivery_li = $(value).parent().parent();
            //save terminal_html to localstorage
            localStorage.setItem("terminal_delivery_html", terminal_html);
            //append to li
            terminal_delivery_li.append(terminal_html);
          });
        } else {
          //find parent li
          var terminal_delivery_li = terminal_delivery_html.parent().parent();
          //save terminal_html to localstorage
          localStorage.setItem("terminal_delivery_html", terminal_html);
          //append to li
          terminal_delivery_li.append(terminal_html);
        }
      }
    }
  });
};

/**
 * clearCurrentFields
 */
let clearCurrentFields = () => {
  jQuery(document).ready(function ($) {
    //set timeout
    setTimeout(() => {
      //clear current country and state
      $('select[name="terminal_custom_shipping_state2"]').val("");
      //select2 update
      $('select[name="terminal_custom_shipping_state2"]').select2({
        placeholder: "Select State",
        // allowClear: true,
        width: "100%"
      });
    }, 1000);
  });
};

////////////////////////////////////////////////////////////////
jQuery(document).ready(function ($) {
  //get woocommerce state select
  var { state_options, data_options } = wooSelectElementOptions($);
  //append to billing_country_field
  $("#billing_country_field").after(`
        <p class="form-row address-field validate-required validate-state form-row-wide woocommerce-validated" id="terminal_custom_shipping_state2">
          <label for="terminal_custom_shipping_state2">State <abbr class="required" title="required">*</abbr></label>
          <span class="woocommerce-input-wrapper">
            <select name="terminal_custom_shipping_state2" class="state_select">
                ${state_options}
            </select>
          </span>
        </p>
      `);

  //session storage
  sessionStorage.setItem("update_checkout_timer", "0");
  $(document.body).on("update_checkout", function () {
    //get session storage and check if its 2
    var update_checkout_timer = sessionStorage.getItem("update_checkout_timer");
    //convert to int
    var update_checkout_timer_int = parseInt(update_checkout_timer);
    //check if its 2
    if (update_checkout_timer_int >= 2) {
      //check if t-update exist
      setTimeout(() => {
        restoreCarriers();
        //select2 update
        $('select[name="terminal_custom_shipping_state2"]').select2({
          placeholder: "Select State",
          // allowClear: true,
          width: "100%"
        });
        terminalButton();
      }, 700);
      //reset session storage
      sessionStorage.setItem("update_checkout_timer", "0");
    }
    //increment session storage
    sessionStorage.setItem(
      "update_checkout_timer",
      update_checkout_timer_int + 1
    );
  });

  $('select[name="terminal_custom_shipping_state2"]').change(function (e) {
    e.preventDefault();
    var state = $(this).val();
    //process updateCoreWoocommerceElements
    updateCoreWoocommerceElements(state, "");
    //check if billing_country exist
    if ($('select[name="billing_country"]').length > 0) {
      var countrycode = $('select[name="billing_country"]').val();
    } else {
      var countrycode = $('input[name="billing_country"]').val();
    }
    var lga = "";
    //if countrycode and state is empty
    if (countrycode == "" || state == "") {
      //show error
      Swal.fire({
        icon: "error",
        title: "Please select a country and state",
        text: "Country and state is required",
        confirmButtonColor: "rgb(246 146 32)",
        cancelButtonColor: "rgb(0 0 0)",
        //footer
        footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
      });
      return;
    }
    termianlDataParcel.clearCarrierData();
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_get_cities",
        countryCode: countrycode,
        stateCode: state,
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: function () {
        //block form name="checkout"
        $("#order_review").block({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6
          }
        });
      },
      success: function (response) {
        //unblock
        $("#order_review").unblock();
        //check if response code 200
        if (response.code != 200) {
          //swal
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: response.message,
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            //footer
            footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
          });
          return;
        }
        //stringify
        var cities = JSON.stringify(response.cities);
        //save to local storage response.cities
        localStorage.setItem("terminal_delivery_cities", cities);
        do_terminal_calculation(response.cities);
      },
      error: function (xhr, status, error) {
        //swal
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong!: " + xhr.responseText,
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
          //footer
          footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
        });
      }
    });
  });

  //check if billing_country exist
  if ($('select[name="billing_country"]').length > 0) {
    //on change billing_country
    $('select[name="billing_country"]').change(function (e) {
      e.preventDefault();
      var country = $(this).val();
      //reset carrier data
      termianlDataParcel.clearCarrierData();
      //ajax to get states
      $.ajax({
        type: "GET",
        url: terminal_africa.ajax_url,
        data: {
          action: "terminal_africa_get_states",
          countryCode: country,
          nonce: terminal_africa.nonce
        },
        dataType: "json",
        beforeSend: function () {
          //block form name="checkout"
          $("#order_review").block({
            message: null,
            overlayCSS: {
              background: "#fff",
              opacity: 0.6
            }
          });
        },
        success: function (response) {
          //unblock
          $("#order_review").unblock();
          //check if response code 200
          if (response.code != 200) {
            //swal
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: response.message,
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              //footer
              footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
            });
            return;
          }
          var states = response.states;
          //options
          var options = "<option value=''>Select State</option>";
          //loop through states
          for (var i = 0; i < states.length; i++) {
            var state = states[i];
            options += `<option value="${state.isoCode}">${state.name}</option>`;
          }
          //update state select name terminal_custom_shipping_state2
          $('select[name="terminal_custom_shipping_state2"]').html(options);
          //update select2
          $('select[name="terminal_custom_shipping_state2"]').select2({
            placeholder: "Select State",
            // allowClear: true,
            width: "100%"
          });
          //clear select name terminal_custom_shipping_lga2
          $('select[name="terminal_custom_shipping_lga2"]').html("");
          //update select2
          $('select[name="terminal_custom_shipping_lga2"]').select2({
            placeholder: "Select LGA",
            // allowClear: true,
            width: "100%"
          });
        },
        error: function () {
          //error
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Something went wrong...",
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            //footer
            footer: `
                    <div>
                        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
          });
        }
      });
    });
  }

  //checking
  setInterval(() => {
    $("#billing_state_field").hide();
    $("#billing_city_field").hide();
    $("#terminal_custom_shipping_lga2").show();
    $("#terminal_custom_shipping_state2").show();

    //check if billing_postcode_field is after #billing_phone_field
    if (
      $("#billing_postcode_field").prev().attr("id") != "billing_phone_field"
    ) {
      //move billing_postcode_field to after #billing_phone_field
      $("#billing_postcode_field").insertAfter("#billing_phone_field");
    }

    //get label for terminal_custom_shipping_lga2
    var label = $("label[for='terminal_custom_shipping_lga2']");
    //check if label already has class woocheckout-city-label
    if (!label.hasClass("woocheckout-city-label")) {
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html.length) {
        //do nothing
        return;
      }
      //replace with
      label.replaceWith(`
      <label for="terminal_custom_shipping_lga2" class="woocheckout-city-label">
      <span>
        City <abbr class="required" title="required">*</abbr>
      </span>
      <b class="t-restore terminal-woo-checkout-get-rate" onclick="reloadCarrierData(event)"><img src="${terminal_africa.plugin_url}/img/logo-footer.png" align="left" /> Get Shipping Rates</b>
      </label>
      `);
    }
  }, 300);

  //check if billing_country exist
  if ($('select[name="billing_country"]').length > 0) {
    $('select[name="billing_country"]').val("");
    //destroy select2
    // $('select[name="billing_country"]').select2("destroy");
    //init select2
    $('select[name="billing_country"]').select2({
      placeholder: "Select Country",
      // allowClear: true,
      width: "100%"
    });
  }
  //set timeout
  clearCurrentFields();
});

//remove old local storage terminal_delivery_html
localStorage.removeItem("terminal_delivery_html");

jQuery(document).ready(function ($) {
  var initPaymentStatus = () => {
    //check if elem exist .terminal-africa-payment-status
    const terminalAfricaPaymentStatus = $(".terminal-africa-payment-status");

    //check if elem does not exist
    if (!terminalAfricaPaymentStatus) return false;

    //get the current url
    var current_url = window.location.href;
    //check if current url contain checkout
    if (!current_url.includes("checkout")) return false;

    //get order id
    var order_id = $(".terminal-africa-payment-status").data("order-id");

    //check the id is not empty
    if (order_id == "" || order_id == null || order_id == undefined) {
      //do nothing
      return false;
    }

    //send request to get payment status
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_payment_status",
        order_id,
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: function () {
        //block ui
        terminalAfricaPaymentStatus.block({
          message: "",
          css: {
            border: "none",
            padding: "15px",
            backgroundColor: "#000",
            color: "#fff"
          },
          overlayCSS: {
            background: "#fff",
            opacity: 0.5
          }
        });
      },
      success: function (response) {
        //unblock ui
        terminalAfricaPaymentStatus.unblock();
        //update the dom
        terminalAfricaPaymentStatus.text(response.data.status);
      },
      error: function (xhr, status, error) {
        //unblock ui
        terminalAfricaPaymentStatus.unblock();
        //update the dom
        terminalAfricaPaymentStatus.text(
          "Something went wrong: " + xhr.responseText
        );
      }
    });
  };

  //init payment status
  initPaymentStatus();
});

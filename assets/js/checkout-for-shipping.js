////////////////////////////// Terminal Africa Checkout ////////////////////////////
let updateCoreWoocommerceElements_terminalShipping = (
  state = "",
  finaltext = ""
) => {
  jQuery(document).ready(function ($) {
    //find select[name='shipping_state'] option with value and set it to selected
    $('select[name="shipping_state"]')
      .find("option")
      .each(function (index, element) {
        if ($(element).val() == state) {
          let element2 = document.querySelector(
            'select[name="shipping_state"]'
          );
          element2.value = state;
          element2.dispatchEvent(new Event("change"));
        } else {
          $(element).removeAttr("selected");
        }
      });
    //get selected option
    var selected_option = $('select[name="shipping_state"]')
      .find("option:selected")
      .val();
    document.querySelector("#shipping_city").value = finaltext;
    //form name="checkout" input name shipping_city
    //custom
    document.querySelector(
      'form[name="checkout"] input[name="shipping_city"]'
    ).value = finaltext;
    //state
    if (
      document.querySelector(
        'form[name="checkout"] input[name="shipping_state"]'
      )
    ) {
      document.querySelector(
        'form[name="checkout"] input[name="shipping_state"]'
      ).value = selected_option;
    }
  });
};

function terminalsetValue2_terminalShipping(elem) {
  jQuery(document).ready(function ($) {
    //fade in .terminal-woo-checkout-get-rate
    $(".terminal-woo-checkout-get-rate").each(function () {
      $(this).fadeIn();
    });
    var lga = $(elem).val();
    var stateText = $(
      'select[name="terminal_custom_shipping_state2_terminalShipping"]'
    )
      .find("option:selected")
      .text();
    //check if shipping_country exist
    if ($('select[name="shipping_country"]').length > 0) {
      var countryCode = $('select[name="shipping_country"]').val();
    } else {
      var countryCode = $('input[name="shipping_country"]').val();
    }
    var state = $(
      'select[name="terminal_custom_shipping_state2_terminalShipping"]'
    ).val();
    var finaltext = lga + ", " + stateText;

    //process the terminal rates
    var email = $('input[name="shipping_email"]').val();
    var first_name = $('input[name="shipping_first_name"]').val();
    var last_name = $('input[name="shipping_last_name"]').val();
    var phone = $('input[name="shipping_phone"]').val();
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
    //check if phone has +
    if (!phone.includes("+")) {
      //append to phone
      phone = phonecode + phone;
    }

    var line_1 = $('input[name="shipping_address_1"]').val();
    var shipping_postcode = $('input[name="shipping_postcode"]').val() || "--";
    //process updateCoreWoocommerceElements
    updateCoreWoocommerceElements_terminalShipping(state, finaltext);
    //process updateCoreWoocommerceElements for core terminal billing handle
    var lgaCore = $(elem).val();
    var stateTextCore = $('select[name="terminal_custom_shipping_state2"]')
      .find("option:selected")
      .text();
    var stateCore = $('select[name="terminal_custom_shipping_state2"]').val();
    var finaltext = lgaCore + ", " + stateTextCore;
    updateCoreWoocommerceElements(stateCore, finaltext);
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = $(".Terminal-delivery-logo");
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html.length) {
      //do nothing
      return;
    }
    //update global variables
    window.terminal_billing_postcode = shipping_postcode;
    //terminal_shipping_postcode
    window.terminal_shipping_postcode = shipping_postcode;
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
        billing_postcode: shipping_postcode
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
            //process the amount
            let amount = new Intl.NumberFormat("en-US", {
              style: "currency",
              currency: terminal_africa.currency
              //  currencyDisplay: "narrowSymbol",
              //remove decimal
              //  minimumFractionDigits: 0
            }).format(value.amount);
            //set default amount
            let default_amount = value.amount;
            //check if value.default_amount exist
            if (value.default_amount) {
              //set amount to default_amount
              default_amount = value.default_amount;
              //set amount to currency
              amount = new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: value.default_currency
                //  currencyDisplay: "narrowSymbol",
                //remove decimal
                //  minimumFractionDigits: 0
              }).format(default_amount);
            }
            //append to terminal_html
            terminal_html += `
                <div class="t-checkout-single" onclick="terminalSetShippingCrarrier(this, event)" data-carrier-name="${value.carrier_name}" data-amount="${default_amount}" data-duration="${value.delivery_time}" data-pickup="${value.pickup_time}" data-rateid="${value.rate_id}" data-image-url="${value.carrier_logo}">
                <label for="shipping">
                <div style="display: flex;justify-content: start;align-items: center;    padding: 10px;">
                  <img class="Terminal-carrier-delivery-logo" alt="${value.carrier_name}" title="${value.carrier_name}" style="width: auto;height: auto;margin-right: 10px;    max-width: 30px;" src="${value.carrier_logo}">
                  <p style=""> 
                        <span style="font-weight: bolder;">${value.carrier_name}</span> - ${amount} - ${value.delivery_time}
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

//get Woocommerce state select
let wooSelectElementOptions_terminalShipping = ($) => {
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
  var wc_state_options = $("select[name='shipping_state']").find("option");
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
      valueOfElement.value == terminal_shipping_state ? "selected" : ""
    }>${valueOfElement.state}</option>`;
  });
  //return
  return {
    state_options,
    data_options
  };
};

/////// EVENT //////////////////////////////////

let do_terminal_calculation_terminalShipping = (datas, selected = "") => {
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
    //check if terminal_custom_shipping_lga2_terminalShipping element exists
    if (!$("#terminal_custom_shipping_lga2_terminalShipping").length) {
      $("#terminal_custom_shipping_state2_terminalShipping").after(`
        <p class="form-row address-field validate-required validate-state form-row-wide woocommerce-validated" id="terminal_custom_shipping_lga2_terminalShipping" >
          <label for="terminal_custom_shipping_lga2_terminalShipping">City <abbr class="required" title="required">*</abbr></label>
          <span class="woocommerce-input-wrapper">
            <select name="terminal_custom_shipping_lga2_terminalShipping" class="lga_select" style="width: 100% !important;" onchange="terminalsetValue2_terminalShipping(this)">
                ${lga}
            </select>
          </span>
        </p>
      `);
      //check if select2 is added to select[name="terminal_custom_shipping_lga2_terminalShipping"]
      if (
        !$(
          "select[name='terminal_custom_shipping_lga2_terminalShipping']"
        ).hasClass("select2-hidden-accessible")
      ) {
        //select2 init
        $(
          'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
        ).select2({
          placeholder: "Select City",
          // allowClear: true,
          width: "100%"
        });
      } else {
        //destroy and update
        // $('select[name="terminal_custom_shipping_lga2_terminalShipping"]').select2("destroy");
        $(
          'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
        ).select2({
          placeholder: "Select City",
          // allowClear: true,
          width: "100%"
        });
      }
    } else {
      //destroy and update
      // $('select[name="terminal_custom_shipping_lga2_terminalShipping"]').select2("destroy");
      //update select
      $('select[name="terminal_custom_shipping_lga2_terminalShipping"]').html(
        lga
      );
      //update select2
      $(
        'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
      ).select2({
        placeholder: "Select City",
        // allowClear: true,
        width: "100%"
      });
    }

    //recalculate
    $(document.body).trigger("update_checkout");
  });
};

//overide submit button
let terminalButton_terminalShipping = () => {
  jQuery(document).ready(function ($) {
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = $(".Terminal-delivery-logo");
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html.length) {
      //do nothing
      return;
    }

    //check if shipping_country exist
    if ($('select[name="shipping_country"]').length > 0) {
      var countrycode = $('select[name="shipping_country"]').val();
    } else {
      var countrycode = $('input[name="shipping_country"]').val();
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
      var state = $(
        'select[name="terminal_custom_shipping_state2_terminalShipping"]'
      ).val();
      var lga = $(
        'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
      ).val();
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
      //check if terminal_delivery_html is more one
      if (terminal_delivery_html.length > 1) {
        //find parent li for the first index
        var terminal_delivery_li = terminal_delivery_html.first().parent();
      } else {
        //find parent li
        var terminal_delivery_li = terminal_delivery_html.parent();
      }
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
        return;
      }
      //if all is good
      //submit form
      form.submit();
      //clear local storage
      localStorage.removeItem("terminal_delivery_html");
    });
  });
};

let restoreCarriers_terminalShipping = () => {
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

let clearCurrentFields_terminalShipping = () => {
  jQuery(document).ready(function ($) {
    //set timeout
    setTimeout(() => {
      //clear current country and state
      $('select[name="terminal_custom_shipping_state2_terminalShipping"]').val(
        ""
      );
      //select2 update
      $(
        'select[name="terminal_custom_shipping_state2_terminalShipping"]'
      ).select2({
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
  var { state_options, data_options } =
    wooSelectElementOptions_terminalShipping($);
  //append to shipping_country_field
  $("#shipping_country_field").after(`
        <p class="form-row address-field validate-required validate-state form-row-wide woocommerce-validated" id="terminal_custom_shipping_state2_terminalShipping">
          <label for="terminal_custom_shipping_state2_terminalShipping">State <abbr class="required" title="required">*</abbr></label>
          <span class="woocommerce-input-wrapper">
            <select name="terminal_custom_shipping_state2_terminalShipping" class="state_select">
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
        $(
          'select[name="terminal_custom_shipping_state2_terminalShipping"]'
        ).select2({
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

  $('select[name="terminal_custom_shipping_state2_terminalShipping"]').change(
    function (e) {
      e.preventDefault();
      var state = $(this).val();
      //process updateCoreWoocommerceElements
      updateCoreWoocommerceElements(state, "");
      //check if shipping_country exist
      if ($('select[name="shipping_country"]').length > 0) {
        var countrycode = $('select[name="shipping_country"]').val();
      } else {
        var countrycode = $('input[name="shipping_country"]').val();
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
          do_terminal_calculation_terminalShipping(response.cities);
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
    }
  );

  //check if shipping_country exist
  if ($('select[name="shipping_country"]').length > 0) {
    //on change shipping_country
    $('select[name="shipping_country"]').change(function (e) {
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
          //update state select name terminal_custom_shipping_state2_terminalShipping
          $(
            'select[name="terminal_custom_shipping_state2_terminalShipping"]'
          ).html(options);
          //update select2
          $(
            'select[name="terminal_custom_shipping_state2_terminalShipping"]'
          ).select2({
            placeholder: "Select State",
            // allowClear: true,
            width: "100%"
          });
          //clear select name terminal_custom_shipping_lga2_terminalShipping
          $(
            'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
          ).html("");
          //update select2
          $(
            'select[name="terminal_custom_shipping_lga2_terminalShipping"]'
          ).select2({
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
    $("#shipping_state_field").hide();
    $("#shipping_city_field").hide();
    $("#terminal_custom_shipping_lga2_terminalShipping").show();
    $("#terminal_custom_shipping_state2_terminalShipping").show();

    //check if shipping_postcode_field is after #shipping_phone_field
    if (
      $("#shipping_postcode_field").prev().attr("id") != "shipping_phone_field"
    ) {
      //move shipping_postcode_field to after #shipping_phone_field
      $("#shipping_postcode_field").insertAfter("#shipping_phone_field");
    }

    //get label for terminal_custom_shipping_lga2_terminalShipping
    var label = $(
      "label[for='terminal_custom_shipping_lga2_terminalShipping']"
    );
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
      <label for="terminal_custom_shipping_lga2_terminalShipping" class="woocheckout-city-label">
      <span>
        City <abbr class="required" title="required">*</abbr>
      </span>
      <b class="t-restore terminal-woo-checkout-get-rate" onclick="reloadCarrierData_terminalShipping(event)"><img src="${terminal_africa.plugin_url}/img/logo-footer.png" align="left" /> Get Shipping Rates</b>
      </label>
      `);
    }
  }, 300);

  //check if shipping_country exist
  if ($('select[name="shipping_country"]').length > 0) {
    $('select[name="shipping_country"]').val("");
    //destroy select2
    // $('select[name="shipping_country"]').select2("destroy");
    //init select2
    $('select[name="shipping_country"]').select2({
      placeholder: "Select Country",
      // allowClear: true,
      width: "100%"
    });
  }
  //set timeout
  clearCurrentFields_terminalShipping();
});

//remove old local storage terminal_delivery_html
localStorage.removeItem("terminal_delivery_html");

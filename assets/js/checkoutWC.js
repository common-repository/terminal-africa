////////////////////////////// Terminal Africa Checkout ////////////////////////////
/// This is for the checkoutWC wordpress plugin and its checkout page integration
////////////////////////////////////////////////////////////
let terminalCheckoutWC = {
  /**
   * init
   */
  init: () => {
    jQuery(function ($) {
      //Check if shipping is enabled by woocommerce
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html.length) {
        //do nothing
        return;
      }
      //remove old local storage terminal_delivery_html
      localStorage.removeItem("terminal_delivery_html");
      //check if element exist #shipping_country
      let shippingCountry = $("#shipping_country");
      if (!shippingCountry.length) {
        //Swal alert if element not exist
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Country field not found, please contact support",
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
        //return
        return;
      }
      //set country to ''
      shippingCountry.val("");
      //unselect country options
      shippingCountry.find("option:selected").prop("selected", false);
      //get all options
      let shippingCountryOptions = shippingCountry.find("option");
      //options html
      let shippingCountryOptionsHtml = "";
      //loop
      shippingCountryOptions.each(function (index, element) {
        //pass option to html
        shippingCountryOptionsHtml += $(element).prop("outerHTML");
      });
      //get html
      let shippingCountryHtml = shippingCountryOptionsHtml;
      //replace country with
      shippingCountry.replaceWith(`
      <select name="shipping_country" id="shipping_country" class="country_to_state country_select cfw-no-select2" data-persist="false" data-saved-value="NG" data-parsley-required="true" data-parsley-group="cfw-customer-info" autocomplete="country" data-placeholder="Country / Region" data-label="Country / Region" onchange="terminalCheckoutWC.countryOnChange(this,event)">
          ${shippingCountryHtml}
      </select>
      `);
      //check if element exist #shipping_state
      let shippingState = $("#shipping_state");
      if (!shippingState.length) {
        //Swal alert if element not exist
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "State field not found, please contact support",
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
        //return
        return;
      }

      //check if shipping_phone_field is after #shipping_address_1_field
      if (
        $("#shipping_phone_field")
          .parent()
          .prev()
          .find("p#shipping_address_1_field")
          .attr("id") != "shipping_address_1_field"
      ) {
        //move shipping_phone_field to after #shipping_address_1_field
        $("#shipping_phone_field")
          .insertAfter("#shipping_address_1_field")
          .parent();
      }

      //check if element exist #shipping_city
      let shippingCity = $("#shipping_city");
      if (!shippingCity.length) {
        //Swal alert if element not exist
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "City field not found, please contact support",
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
        //return
        return;
      }

      //clear current state selected option value
      shippingState.val("");
      //deselect current state selected option
      shippingState.find("option:selected").prop("selected", false);
      //get woocommerce state select
      var { state_options } = terminalCheckoutWC.overrideStateSelect(
        shippingState,
        $
      );

      //append to billing_country_field
      shippingState.replaceWith(`
          <select name="shipping_state" class="state_select cfw-no-select2" id="shipping_state" onchange="terminalCheckoutWC.stateOnChange(this,event)">
              ${state_options}
          </select>
      `);
      //check if billing_postcode_field is after #billing_phone_field
      if (
        $("#billing_postcode_field").prev().attr("id") != "billing_phone_field"
      ) {
        //move billing_postcode_field to after #billing_phone_field
        $("#billing_postcode_field").insertAfter("#billing_phone_field");
      }

      //Check if shipping is enabled by woocommerce
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html.length) {
        //do nothing
        return;
      }

      //get h3 class cfw-shipping-methods-heading
      let cfw_shipping_methods_heading = $(".cfw-shipping-methods-heading");
      //get the html
      let cfw_shipping_methods_heading_html =
        cfw_shipping_methods_heading.html();
      //replace with
      cfw_shipping_methods_heading.replaceWith(`
       <div class="terminalCheckoutWC-container">
          <div class="terminalCheckoutWC-container-inner">
            <h3 class="cfw-shipping-methods-heading">${cfw_shipping_methods_heading_html}</h3>
          </div>
          <div class="terminalCheckoutWC-container-inner">
            <b class="t-restoreInner" onclick="terminalCheckoutWC.reloadCarrierData(event)"><img src="${terminal_africa.plugin_url}/img/logo-footer.png" /> Get Shipping Rates</b>
          </div>
       </div>
      `);

      //shipping_city to empty
      shippingCity.val("");
    });
  },
  /**
   * countryOnChange
   *
   * country change
   * @param {*} elem
   * @param {*} e
   */
  countryOnChange: (elem, e) => {
    jQuery(document).ready(function ($) {
      e.preventDefault();
      var country = $(elem).val();
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
          //reset carrier data
          termianlDataParcel.clearCarrierData();
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
          var state_options = "<option value=''>Select State</option>";
          //loop through states
          for (var i = 0; i < states.length; i++) {
            var state = states[i];
            state_options += `<option value="${state.isoCode}">${state.name}</option>`;
          }
          //update state select name shipping_state
          let shippingState = $('select[name="shipping_state"]');
          //check if element exist
          if (shippingState.length > 0) {
            $('select[name="shipping_state"]').html(state_options);
          } else {
            $("#shipping_state").replaceWith(`
          <select name="shipping_state" class="state_select cfw-no-select2" id="shipping_state" onchange="terminalCheckoutWC.stateOnChange(this,event)">
              ${state_options}
          </select>
      `);
          }
          //clear select name terminal_custom_shipping_lga2
          let lga = $('select[name="shipping_city"]');
          //check if element exist
          if (lga.length > 0) {
            $('select[name="shipping_city"]').html("");
          }
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
  },
  /**
   * initCartData
   *
   * save terminal cart
   * @param {*} $
   * @returns
   */
  initCartData: ($) => {
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
          if (response.code == 400) {
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
              terminalCheckoutWC.initCartData($);
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
  },
  /**
   * terminalSetShippingCrarrier
   * @param {*} elem
   * @param {*} e
   */
  terminalSetShippingCrarrier: (elem, e) => {
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
            terminalCheckoutWC.restoreCarriers();
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
  },
  /**
   * getLocalGovernments
   * @param {*} country
   * @param {*} state
   */
  getLocalGovernments: (country, state) => {
    jQuery(document).ready(function ($) {
      //reset carrier data
      termianlDataParcel.clearCarrierData();
      //ajax
      $.ajax({
        type: "GET",
        url: terminal_africa.ajax_url,
        data: {
          action: "terminal_africa_get_cities",
          countryCode: country,
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
          //pass data to #shipping_city
          terminalCheckoutWC.passCitiesToShippingCity(response.cities);
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
  },
  /**
   * Pass cities to shipping city
   * @param {*} cities
   * @param {*} selected
   */
  passCitiesToShippingCity: (cities, selected = "") => {
    jQuery(document).ready(function ($) {
      let content = `
          <select name="shipping_city" id="shipping_city" class="state_select cfw-no-select2 garlic-auto-save" data-parsley-trigger="keyup change focusout" data-saved-value="CFW_EMPTY" data-parsley-required="true" data-parsley-group="cfw-customer-info" autocomplete="address-level1" data-placeholder="City" data-input-classes="cfw-no-select2" data-label="City" placeholder="City" onchange="terminalCheckoutWC.cityOnChange(this,event)">
              <option value="">Select City</option>
               ${cities
                 .map((city) => {
                   return `<option value="${city.name}" ${
                     selected == city.name ? "selected" : ""
                   }>${city.name}</option>`;
                 })
                 .join("")}
          </select>
          `;
      //replace with #shipping_city
      $("#shipping_city").replaceWith(content);
      //animate from     margin-top: -10px; to margin-top: 0px;
      $("#shipping_city")
        .css({
          marginTop: "-10px"
        })
        .animate({ marginTop: 0 }, 1000);
    });
  },
  /**
   * restoreCarriers
   *
   * restore carriers button
   */
  restoreCarriers: () => {
    jQuery(document).ready(function ($) {
      //check if local storage is not empty
      if (localStorage.getItem("terminal_delivery_html") != null) {
        //check if t-restore does not exist
        if (!$(".t-restore").length) {
          let terminal_html = `
          <div class="t-checkout-carriers-reload-checkoutWC">
          <div class="t-checkout-carriers t-update">`;
          terminal_html += `<b class="t-restore" onclick="terminalCheckoutWC.restoreCarrierData(this)">Change Carrier</b>`;
          terminal_html += `</div>
          </div>
          `;
          //append to terminal_html
          var terminal_delivery_html = $(".Terminal-delivery-logo");
          //find parent li
          var terminal_delivery_li = terminal_delivery_html
            .parent()
            .parent()
            .parent();
          //append to li
          terminal_delivery_li.append(terminal_html);
        }
      }
    });
  },
  /**
   * restoreCarrierData
   *
   * restore carrier data
   * @param {*} e
   */
  restoreCarrierData: (e) => {
    jQuery(document).ready(function ($) {
      //check if local storage is not empty
      if (localStorage.getItem("terminal_delivery_html") != null) {
        let terminal_html = localStorage.getItem("terminal_delivery_html");
        //append to terminal_html
        var terminal_delivery_html = $(".Terminal-delivery-logo");
        //find parent li
        var terminal_delivery_li = terminal_delivery_html
          .parent()
          .parent()
          .parent();
        //remove .t-checkout-carriers
        terminal_delivery_li.find(".t-checkout-carriers").remove();
        //append to li
        terminal_delivery_li.append(terminal_html);
      }
    });
  },
  /**
   * reloadCarrierData
   *
   * reload carrier data
   * @param {*} e
   */
  reloadCarrierData: (e) => {
    e.preventDefault();
    jQuery(document).ready(function ($) {
      //check if shipping city is available as select and not input
      if (!$("#shipping_city").is("select")) {
        //swal
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select state and city first!",
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
      //check if shipping city is empty
      if ($("#shipping_city").val() == "") {
        //swal
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select state and city first!",
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
      //trigger event change
      $("select[name='shipping_city']").trigger("change");
    });
  },
  /**
   * overrideStateSelect
   *
   * overide state select
   * @param {*} shippingState
   * @param {*} $
   * @returns
   */
  overrideStateSelect: (shippingState, $) => {
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
    var wc_state_options = shippingState.find("option");
    wc_state_options.each(function (index, element) {
      var state_value = $(element).val();
      var state_name = $(element).text();

      //if state_name is undefined skip
      if (state_name === undefined) {
        return;
      }
      //push to data_options
      data_options.state.push({
        state: state_name,
        value: state_value
      });
    });
    //array unique
    var unique_state = [...new Set(data_options.state)];
    var state_options = "";
    $.each(unique_state, function (indexInArray, valueOfElement) {
      state_options += `<option value="${valueOfElement.value}" ${
        valueOfElement.value == terminal_billing_state ? "" : ""
      }>${valueOfElement.state}</option>`;
    });
    //return
    return {
      state_options,
      data_options
    };
  },
  /**
   * stateOnChange
   *
   * state on change event
   * @param {*} elem
   * @param {*} e
   */
  stateOnChange: (elem, e) => {
    jQuery(function ($) {
      //get country value
      let country = $("#shipping_country").val();
      //get state selected option value
      let state = $(elem).val();
      //check if value is not empty
      if (country == "") {
        //show alert
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select a country",
          footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
        });
        //return
        return;
      }
      //get local governments by details
      terminalCheckoutWC.getLocalGovernments(country, state);
    });
  },
  /**
   * cityOnChange
   *
   * city on change event
   * @param {*} elem
   * @param {*} e
   */
  cityOnChange: (elem, e) => {
    jQuery(document).ready(function ($) {
      //Check if shipping is enabled by woocommerce
      var terminal_delivery_html = $(".Terminal-delivery-logo");
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html.length) {
        //do nothing
        return;
      }
      //reset carrier data
      termianlDataParcel.clearCarrierData();
      //get country value
      let country = $("#shipping_country").val();
      //get state selected option value
      let state = $("#shipping_state").val();
      //get city selected option value
      let city = $(elem).val();
      //check if value is not empty
      if (country == "") {
        //show alert
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select a country",
          footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
        });
        //return
        return;
      }
      //check if value is not empty
      if (state == "") {
        //show alert
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select a state",
          footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
        });
        //return
        return;
      }
      //getCustomerDetails
      let customer_details = terminalCheckoutWC.getCustomerDetails($);
      //append to customer_details
      customer_details.city = city;
      //state
      customer_details.state = state;
      //country
      customer_details.country = country;
      //check if customer_details is empty
      if (Object.keys(customer_details).length === 0) {
        //show alert
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please enter all required fields",
          footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
        });
        //return
        return;
      }
      //reset carrier data
      termianlDataParcel.clearCarrierData();
      //get terminal shipping rate
      terminalCheckoutWC.getTerminalShippingRate(customer_details);
    });
  },
  /**
   * getCustomerDetails
   *
   * get customer details
   * @param {*} $
   * @returns
   */
  getCustomerDetails: ($) => {
    //get customer details
    let customer_details = {};
    //check if element exist #shipping_full_name
    if ($("#shipping_full_name").length) {
      let fullName = $("#shipping_full_name").val();
      //split full name
      let splitFullName = fullName.split(" ");
      //get first name
      let firstName = splitFullName[0];
      //get last name
      let lastName = splitFullName[1];
      //add to customer_details
      customer_details.firstName = firstName;
      customer_details.lastName = lastName;
    } else {
      //check if element exist #shipping_first_name
      if ($("#shipping_first_name").length) {
        let firstName = $("#shipping_first_name").val();
        //add to customer_details
        customer_details.firstName = firstName;
      }
      //check if element exist #shipping_last_name
      if ($("#shipping_last_name").length) {
        let lastName = $("#shipping_last_name").val();
        //add to customer_details
        customer_details.lastName = lastName;
      }
    }
    //check if element exist #shipping_phone
    if ($("#shipping_phone").length) {
      let phone = $("#shipping_phone").val();
      //add to customer_details
      customer_details.phone = phone;
    }
    //check if element exist #billing_email
    if ($("#billing_email").length) {
      let billing_email = $("#billing_email").val();
      //add to customer_details
      customer_details.email = billing_email;
    }
    //check if element exist #shipping_address_1
    if ($("#shipping_address_1").length) {
      let shipping_address_1 = $("#shipping_address_1").val();
      //add to customer_details
      customer_details.address = shipping_address_1;
    }
    //check if element exist #shipping_postcode
    if ($("#shipping_postcode").length) {
      let shipping_postcode = $("#shipping_postcode").val();
      //add to customer_details
      customer_details.postcode = shipping_postcode;
    }

    //confirm customer details are not empty
    if (
      customer_details.firstName == "" ||
      customer_details.lastName == "" ||
      customer_details.phone == "" ||
      customer_details.email == "" ||
      customer_details.address == "" ||
      customer_details.postcode == ""
    ) {
      //show alert
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Please fill all required fields",
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      //return
      return {};
    }

    //return
    return customer_details;
  },
  /**
   * getTerminalShippingRate
   * @param {*} customer_details
   */
  getTerminalShippingRate: (customer_details) => {
    jQuery(function ($) {
      //reset carrier data
      termianlDataParcel.clearCarrierData();
      //get terminal countries
      let tm_countries = terminal_africa.terminal_africal_countries;
      //get country
      let countryCode = customer_details.country;
      //find country where isoCode is NG
      let tm_country = tm_countries.find(
        (country) => country.isoCode === countryCode
      );
      //get state
      let state = $("select[name='shipping_state'] option:selected").val();
      //get city
      let lga = customer_details.city;
      //get email
      let email = customer_details.email;
      //get first_name
      let first_name = customer_details.firstName;
      //get last_name
      let last_name = customer_details.lastName;
      //get phone
      let phone = customer_details.phone;
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
      if (phone && !phone.includes("+")) {
        //append to phone
        phone = phonecode + phone;
      }
      //get line_1
      let line_1 = customer_details.address;
      //get billing_postcode
      let billing_postcode = customer_details.postcode;
      //get stateText
      let stateText = $("select[name='shipping_state'] option:selected").text();
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
          <div class="t-checkout-carriers-checkoutWC">
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
                    (value.default_amount *
                      terminalAfricaPriceMarkUpPercentage) /
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
                <div class="t-checkout-single" onclick="terminalCheckoutWC.terminalSetShippingCrarrier(this, event)" data-carrier-name="${
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
          </div>
          `;
            //append to terminal_html
            var terminal_delivery_html = $(".Terminal-delivery-logo");
            //find parent li
            var terminal_delivery_li = terminal_delivery_html
              .parent()
              .parent()
              .parent();
            //save terminal_html to localstorage
            localStorage.setItem("terminal_delivery_html", terminal_html);
            //append to li
            terminal_delivery_li.append(terminal_html);
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
    });
  },
  /**
   * checkForCarriers
   */
  checkForCarriers: function () {
    jQuery(document).ready(function ($) {
      //check if shipment is applied
      let img = $(".Terminal-delivery-logo");
      //check if parent has element .woocommerce-Price-amount amount
      if (!img.parent().find(".woocommerce-Price-amount").length) {
        //check if class exist woocommerce-Price-amount
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
      } else {
        //remove block from
        $(".cfw-place-order-wrap").unblock();
      }
    });
  },
  //post code focused out
  postCodeFocusedOut: function (e) {
    //prevent default
    e.preventDefault();
    //check if shipping_state is empty
    if (jQuery("#shipping_state").val() == "") {
      //do nothing
      return;
    }
    terminalCheckoutWC.reloadCarrierData(e);
  },
  /**
   * setCarrierLogo
   *
   * set carrier logo
   */
  setCarrierLogo: function () {
    //set interval carrier logo
    setInterval(function () {
      jQuery(document).ready(function ($) {
        //Check if shipping is enabled by woocommerce
        var terminal_delivery_html = $(".Terminal-delivery-logo");
        //check if terminal_delivery_html exist
        if (!terminal_delivery_html.length) {
          //do nothing
          return;
        }
        //check if local storage is not empty
        if (localStorage.getItem("terminal_carrier_logo") != null) {
          let terminal_carrier_logo = localStorage.getItem(
            "terminal_carrier_logo"
          );
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
        //label for shipping_postcode
        let labelTerminal = $("label[for='shipping_postcode']");
        //remove span with class optional
        labelTerminal.find("span.optional").remove();
        //check if class terminal-postcode-wc-checkout exist on input#shipping_postcode
        if (
          !$("input#shipping_postcode").hasClass(
            "terminal-postcode-wc-checkout"
          )
        ) {
          //replace with
          $("input#shipping_postcode").replaceWith(
            '<input type="text" class="input-text garlic-auto-save terminal-postcode-wc-checkout" onfocusout="terminalCheckoutWC.postCodeFocusedOut(event)" name="shipping_postcode" id="shipping_postcode" placeholder="Postcode required" value="" data-parsley-length="[2,12]" data-parsley-trigger="change focusout" data-parsley-postcode="true" data-parsley-debounce="200" data-saved-value="CFW_EMPTY" autocomplete="postal-code" data-placeholder="Postcode" data-parsley-id="26">'
          );
        }
        //restoreCarriers
        terminalCheckoutWC.restoreCarriers();
        //get session
        let session = sessionStorage.getItem("billing_phone_terminal");
        //check if session is empty
        if (session == null) {
          //check if the element exist #billing_phone_terminal
          if ($("#shipping_phone").length > 0) {
            // console.log("focus");
            //add on focusout
            $("#shipping_phone").on("focusout", function () {
              // console.log("focus out");
              //focus out #billing_phone_terminal
              terminalCheckoutWC.terminalPhoneKeyup();
            });
            //set session
            sessionStorage.setItem("billing_phone_terminal", "true");
          }
        }
      });
    }, 800);

    //check if page url match 'order-received'
    if (window.location.href.indexOf("order-received") > -1) {
      //do nothing
    } else {
      //set interval
      setInterval(() => {
        jQuery(document).ready(function ($) {
          //Check if shipping is enabled by woocommerce
          var terminal_delivery_html = $(".Terminal-delivery-logo");
          //check if terminal_delivery_html exist
          if (!terminal_delivery_html.length) {
            //do nothing
            return;
          }
          //check if #shipping_postcode_field display none
          if ($("#shipping_postcode_field").css("display") == "none") {
            //add value to post code
            $("#shipping_postcode").val(terminal_shipping_postcode);
            //fade in #shipping_postcode_field
            $("#shipping_postcode_field").show();
          }
          //check if .terminal-overlay-checkout exist
          if ($(".terminal-overlay-checkout").length == 0) {
            //unblock .cfw-place-order-wrap
            $(".cfw-place-order-wrap").unblock();
            let img = $(".Terminal-delivery-logo");
            //check if parent has element .woocommerce-Price-amount amount
            if (!img.parent().find(".woocommerce-Price-amount").length) {
              //add overlay html to .cfw-place-order-wrap with opacity
              $(".cfw-place-order-wrap").block({
                message: `
              <div class="terminal-overlay-checkout" onclick="terminalCheckoutWC.checkForCarriers(this,event)">
                <div class="terminal-overlay-content-checkout">
                  
                </div>
              </div>
              `,
                css: {
                  border: "none",
                  padding: "0px",
                  backgroundColor: "transparent",
                  color: "#fff",
                  opacity: 0.3,
                  width: "100%",
                  height: "100%"
                }
              });
            } else {
              //unblock .cfw-place-order-wrap
              $(".cfw-place-order-wrap").unblock();
            }
          } else {
            //check if cfw-place-order-wrap has a element with class blockUI blockOverlay
            if ($(".cfw-place-order-wrap").find(".blockUI.blockOverlay")) {
              //remove blockUI blockOverlay
              $(".cfw-place-order-wrap").find(".blockUI.blockOverlay").hide();
            }
          }
        });
      }, 300);
      //add value to post code
      jQuery("#shipping_postcode").val(terminal_shipping_postcode);
    }
  }
};

if (window.location.href.indexOf("order-received") > -1) {
  //do nothing
} else {
  setTimeout(() => {
    //init
    terminalCheckoutWC.init();
  }, 2000);

  //init cart data
  jQuery(function ($) {
    //init cart data
    terminalCheckoutWC.initCartData($);
    //set carrier logo
    terminalCheckoutWC.setCarrierLogo();
  });
}

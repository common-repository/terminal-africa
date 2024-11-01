////////////////////////////// Terminal Africa Checkout ////////////////////////////
/// This is for the fluidCheckout wordpress plugin and its checkout page integration
////////////////////////////////////////////////////////////

/**
 * Fluid extension for terminal shipment plugin
 */
class FluidCheckoutTerminal {
  constructor() {
    this.$ = jQuery;
    //init
    this.init();
  }

  /**
   * Init function
   */
  init() {
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = this.isTerminalRunning();
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html) {
      //do nothing
      return;
    }
    //remove old local storage terminal_delivery_html
    localStorage.removeItem("terminal_delivery_html");
    //setDefaultCountryAndStateToEmpty
    this.setDefaultCountryAndStateToEmpty();
    //init interval
    this.setInterval();
  }

  /**
   * Set interval for 3 milliseconds
   */
  setInterval() {
    setInterval(() => {
      //add event to country selection
      this.addEventToCountrySelection();
      //switchTownCityWithState
      this.switchTownCityWithState();
      //addEventToStateSelect
      this.addEventToStateSelect();
      //setShippingCodeIfEmpty
      this.setShippingCodeIfEmpty();
      //removeElementWithClassTCheckoutCarriers
      // this.removeElementWithClassTCheckoutCarriers();
      //setTerminalCarriersHTML
      this.setTerminalCarriersHTML();
      //replaceShipmentMethodTitle
      this.replaceShipmentMethodTitle();
      //addGetCityButton
      this.addGetCityButton();
      //blockPlaceOrderButtonClick
      this.blockPlaceOrderButtonClick();
    }, 300);
  }

  /**
   * Switch Town / City with State
   */
  switchTownCityWithState() {
    //check if shipping_state_field is after shipping_city_field
    if (
      this.$("#shipping_state_field").index() >
      this.$("#shipping_city_field").index()
    ) {
      //switch shipping_state_field with shipping_city_field
      this.$("#shipping_state_field").after(this.$("#shipping_city_field"));
    }
    //check if class fluid-checkout-state exist
    if (!this.$("#shipping_state_field").hasClass("fluid-checkout-state")) {
      this.$("#shipping_state_field").addClass("fluid-checkout-state");
    }
    //check if class fluid-checkout-city exist
    if (!this.$("#shipping_city_field").hasClass("fluid-checkout-city")) {
      this.$("#shipping_city_field").addClass("fluid-checkout-city");
    }
  }

  /**
   * Block place order button click
   * @return void
   */
  blockPlaceOrderButtonClick() {
    //check if .terminal-overlay-checkout exist
    if (this.$(".terminal-overlay-checkout").length == 0) {
      //Check if shipping is enabled by woocommerce
      var terminal_delivery_html = this.isTerminalRunning(true);
      //check if terminal_delivery_html exist
      if (!terminal_delivery_html) {
        //do nothing
        return;
      }
      //check if parent has element .woocommerce-Price-amount amount
      if (
        !terminal_delivery_html.parent().find(".woocommerce-Price-amount")
          .length
      ) {
        //check if the place order button has type button
        if (this.$("#place_order").attr("type") == "button") {
          //do nothing
          return;
        }
        //change type to button
        this.$("#place_order").replaceWith(
          `
            <button type="button" class="button alt fc-place-order-button" name="woocommerce_checkout_place_order" id="place_order" value="Place order" data-value="Place order" onclick="terminalFluidCheckout.checkForCarriers(this,event)">Place order</button>
          `
        );
      } else {
        //change type to submit
        this.$("#place_order").attr("type", "submit");
      }
    } else {
      //change type to submit
      this.$("#place_order").attr("type", "submit");
      //check if cfw-place-order-wrap has a element with class blockUI blockOverlay
      if (this.$("#place_order").find(".blockUI.blockOverlay")) {
        //remove blockUI blockOverlay
        this.$("#place_order").find(".blockUI.blockOverlay").hide();
      }
    }
  }

  /**
   * Check for place order button lock
   * @param {HTMLElement} elem
   * @param event
   * @return void
   */
  checkForCarriers(elem, e) {
    //prevent default
    e.preventDefault();
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = this.isTerminalRunning(true);
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html) {
      //do nothing
      return;
    }
    //check if parent has element .woocommerce-Price-amount amount
    if (
      !terminal_delivery_html.parent().find(".woocommerce-Price-amount").length
    ) {
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
      //submit form name checkout
      this.$("form[name=checkout]").submit();
    }
  }

  /**
   * Determine terminal is running on checkout page
   * @return boolean
   */
  isTerminalRunning(elem = false) {
    var terminalIsActive = false;
    var elemenData;
    //get all .shipping_method class
    var shipping_method = this.$(".shipping_method");
    //check the element id match with terminal_delivery
    shipping_method.each((index, element) => {
      //get element id
      var element_id = this.$(element).attr("id");
      //check if element_id match with terminal_delivery
      if (element_id.includes("terminal_delivery")) {
        //set terminalIsActive to true
        terminalIsActive = true;
        //set elemenData
        elemenData = this.$(element);
      }
    });
    //check if elem is true
    if (elem) {
      //return elemenData
      return elemenData;
    } else {
      //return terminalIsActive
      return terminalIsActive;
    }
  }

  /**
   * Set Terminal Carriers HTML
   * @return void
   */
  setTerminalCarriersHTML() {
    //get all .shipping_method class
    var shipping_method = this.$(".shipping_method");
    //check the element id match with terminal_delivery
    shipping_method.each((index, element) => {
      //get element id
      var element_id = this.$(element).attr("id");
      //check if element_id match with terminal_delivery
      if (element_id.includes("terminal_delivery")) {
        //append to terminal_html
        var terminal_delivery_html = this.$(`#${element_id}`);
        //find parent li
        var terminal_delivery_li = terminal_delivery_html.parent();
        //save terminal_html to localstorage
        var oldCarriers = localStorage.getItem("terminal_delivery_html");
        //check if oldCarriers exist
        if (oldCarriers) {
          //check if element exist .t-checkout-carriers-checkoutFluid
          if (this.$(".t-checkout-carriers-checkoutFluid").length) {
            //check if class exist t-checkout-carriers in t-checkout-carriers-checkoutFluid
            if (
              !this.$(".t-checkout-carriers-checkoutFluid").find(
                ".t-checkout-carriers"
              ).length
            ) {
              //overwrite html
              this.$(".t-checkout-carriers-checkoutFluid").replaceWith(
                oldCarriers
              );
            }
          } else {
            //append to li
            terminal_delivery_li.append(oldCarriers);
          }
        }
      }
    });
  }

  /**
   * Add Get City Button
   * @return void
   */
  addGetCityButton() {
    //check if class exist terminalGetShippingCities
    if (this.$(".terminalGetShippingCities").length) return false;
    //get label with for="shipping_city"
    let label = this.$("label[for='shipping_city']");
    //check if label exist
    if (label.length) {
      //get old html
      let old_html = label.html();
      //replace with new html
      label.html(`
      <div class="terminalGetShippingCities">
         <div>
            ${old_html}
         </div>
         <div>
            <button class="button" onclick="terminalFluidCheckout.getCity(event)">
             <img src="${terminal_africa.plugin_url}/img/logo-footer.png" align="left" /> Get Cities
            </button>
         </div>
      </div>
      `);
    }
  }

  /**
   * Get City on click
   * @param {*} event
   */
  getCity(event) {
    //prevent default
    event.preventDefault();
    //get shipping state
    let shipping_state = this.$("#shipping_state_custom");
    //check if element exist
    if (shipping_state.length) {
      //check if value is empty
      if (shipping_state.val() == "") {
        //show error
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please select a state first",
          footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
        });
      }
      //trigger change
      shipping_state.trigger("change");
      //return
      return;
    }
    //show error
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "Please select a state first",
      footer: `
      <div>
        <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
      </div>
    `
    });
  }

  /**
   * Replace Shipment Method Title
   */
  replaceShipmentMethodTitle() {
    //check if element exist .terminalFluid-container
    if (this.$(".terminalFluid-container").length > 0) {
      return;
    }
    //get h3 class fc-step__substep-title--shipping_method
    let fluidCheckoutMehtodTitle = this.$(
      ".fc-step__substep-title--shipping_method"
    );
    //get the html
    let fluidCheckoutMehtodTitle_html = fluidCheckoutMehtodTitle.html();
    //replace with
    fluidCheckoutMehtodTitle.replaceWith(`
       <div class="terminalFluid-container">
          <div class="terminalFluid-container-inner">
            <h3 class="fc-step__substep-title--shipping_method">${fluidCheckoutMehtodTitle_html}</h3>
          </div>
          <div class="terminalFluid-container-inner t-restoreInner" onclick="terminalFluidCheckout.reloadCarrierData()">
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" align="left" />
            Get Shipping Rates
          </div>
       </div>
      `);
  }

  /**
   * reloadCarrierData
   */
  reloadCarrierData() {
    //check if shipping city is available as select and not input
    if (!this.$("#shipping_city").is("select")) {
      //trigger change to state
      this.$("#shipping_state_custom").trigger("change");
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
    if (this.$("#shipping_city").val() == "") {
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
    //check if this.$("select[name='shipping_city']") exist
    if (this.$("select[name='shipping_city']").length) {
      //trigger event change
      this.$("select[name='shipping_city']").trigger("change");
    } else {
      //trigger change to state
      this.$("#shipping_state_custom").trigger("change");
      //show error
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Please select state and city again!",
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
  }

  /**
   * Remove element with class t-checkout-carriers-checkoutFluid
   */
  removeElementWithClassTCheckoutCarriers() {
    //check if class t-checkout-carriers-checkoutFluid exist
    if (this.$(".t-checkout-carriers-checkoutFluid").length) {
      //remove class t-checkout-carriers-checkoutFluid
      this.$(".t-checkout-carriers-checkoutFluid").remove();
    }
  }

  /**
   * setShippingCodeIfEmpty
   */
  setShippingCodeIfEmpty() {
    //check if shipping_postcode_field exist
    if (this.$("#shipping_postcode_field").length) {
      //get parent of shipping_postcode_field and remove all class
      this.$("#shipping_postcode_field")
        .parent()
        .parent()
        .parent()
        .removeClass();
      //check if display is none
      if (this.$("#shipping_postcode_field").css("display") == "none") {
        //get terminal_default_zip_code
        var terminal_default_zip_code = localStorage.getItem(
          "terminal_default_zip_code"
        );
        //replace with new shipping_postcode_field
        this.$("#shipping_postcode_field").replaceWith(
          `
          <p class="form-row address-field form-row-wide" id="shipping_postcode_field" data-priority="90" data-o_class="form-row " style="display: block;width:100% !important;">
            <label for="shipping_postcode" class="">Postcode&nbsp;<abbr class="required" title="required">*</abbr></label>
            <span class="woocommerce-input-wrapper">
              <input type="text" class="input-text " name="shipping_postcode" data-autofocus="" required id="shipping_postcode" value="" data-autocomplete="shipping postal-code" autocomplete="shipping postal-code" placeholder="Postcode / ZIP" onfocusout="terminalFluidCheckout.postCodeFocusedOut(event)" value="${terminal_default_zip_code}">
            </span>
          </p>
          `
        );
      } else {
        //check if label has span.optional
        if (
          this.$("#shipping_postcode_field").find("label").find("span.optional")
        ) {
          //replacewith
          this.$("#shipping_postcode_field").find("label").find("span.optional")
            .replaceWith(`
            <abbr class="required" title="required">*</abbr>
            `);
          //set placeholder to Postcode / ZIP
          this.$("#shipping_postcode_field")
            .find("input")
            .attr("placeholder", "Postcode / ZIP");
          //check if shipping_postcode_field has form-row-first class
          if (this.$("#shipping_postcode_field").hasClass("form-row-first")) {
            //remove and add class form-row-wide
            this.$("#shipping_postcode_field")
              .removeClass("form-row-first")
              .addClass("form-row-wide");
          }
        }
      }
    }
    //check if shipping_postcode_field prev element id is shipping_address_1_field
    if (
      this.$("#shipping_postcode_field").prev().attr("id") !=
      "shipping_address_1_field"
    ) {
      //switch shipping_postcode_field with shipping_address_1_field
      this.$("#shipping_address_1_field").after(
        this.$("#shipping_postcode_field")
      );
    }
  }

  /**
   * Postcode focused out
   * @param {*} event
   * @returns
   */
  postCodeFocusedOut(event) {
    //prevent default
    event.preventDefault();
    //check if shipping_state is empty
    if (jQuery("#shipping_state").val() == "") {
      //do nothing
      return;
    }
    //reload carrier
    this.reloadCarrierData();
  }

  /**
   * Set default country and state to empty
   */
  setDefaultCountryAndStateToEmpty() {
    //get country select
    var country_select = this.$("#shipping_country");
    //get state select
    var state_select = this.$("#shipping_state");
    //check if country_select is select element
    if (country_select.is("select")) {
      //set the default country to empty
      country_select.val("");
      //deselect the country selected option
      country_select.find("option:selected").prop("selected", false);
    }
    //check if state_select is select element
    if (state_select.is("select")) {
      //set the default state to empty
      state_select.val("");
      //deselect the state selected option
      state_select.find("option:selected").prop("selected", false);
    }
  }

  /**
   * Add event to state select
   */
  addEventToStateSelect() {
    //get state select
    var state_select = this.$("#shipping_state");
    //get all state options
    var shippingStateOptions = state_select.find("option");
    //options html
    let shippingStateOptionsHtml = "";
    //loop
    shippingStateOptions.each((index, element) => {
      //pass option to html
      shippingStateOptionsHtml += this.$(element).prop("outerHTML");
    });
    //check if class exist .terminal-custom-fluid-added in document
    if (!this.$(".terminal-custom-fluid-added").length) {
      //destroy select2
      state_select.select2("destroy");
      //replace state_select with new state_select
      state_select.replaceWith(
        `
      <select name="shipping_state" id="shipping_state_custom" class="terminal-custom-fluid-added" autocomplete="address-level1" data-placeholder="Select state…" tabindex="-1" aria-hidden="true">
        ${shippingStateOptionsHtml}
      </select>
      `
      );
      //add event to .terminal-custom-fluid-added
      this.$(".terminal-custom-fluid-added").on(
        "change",
        this.stateOnchangeEvent.bind(this, ".terminal-custom-fluid-added")
      );
    }
  }

  /**
   * Add event to country selection
   */
  addEventToCountrySelection() {
    //get country select
    var country_select = this.$("#shipping_country");
    //get all country options
    var shippingcountryOptions = country_select.find("option");
    //options html
    let shippingcountryOptionsHtml = "";
    //loop
    shippingcountryOptions.each((index, element) => {
      //pass option to html
      shippingcountryOptionsHtml += this.$(element).prop("outerHTML");
    });
    //check if class exist .terminal-custom-fluid-country-added in document
    if (!this.$(".terminal-custom-fluid-country-added").length) {
      //destroy select2
      country_select.select2("destroy");
      //replace country_select with new country_select
      country_select.replaceWith(
        `
      <select name="shipping_country" id="shipping_country_custom" class="terminal-custom-fluid-country-added" autocomplete="address-level1" data-placeholder="Select country…" tabindex="-1" aria-hidden="true">
        ${shippingcountryOptionsHtml}
      </select>
      `
      );
      //add event to .terminal-custom-fluid-country-added
      this.$(".terminal-custom-fluid-country-added").on(
        "change",
        this.countryOnchangeEvent.bind(
          this,
          ".terminal-custom-fluid-country-added"
        )
      );
    }
  }

  /**
   * Add event to state select
   * @param {HTMLElement} elem
   */
  stateOnchangeEvent(elem) {
    //get the value
    var state = this.$(elem).val();
    //get the country
    var country = this.$("select[name='shipping_country']");
    //check if country exists
    if (country.length > 0) {
      country = country.val();
    } else {
      country = this.$("input[name='shipping_country']").val();
    }
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
    //reset carrier data
    termianlDataParcel.clearCarrierData();
    //get local governments by details
    this.getLocalGovernments(country, state);
  }

  /**
   * countryOnchangeEvent
   * @param {HTMLElement} elem
   */
  countryOnchangeEvent(elem) {
    //get the value
    var country = this.$(elem).val();
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
    //reset carrier data
    termianlDataParcel.clearCarrierData();
    //get state
    this.countryOnChange(country);
  }

  /**
   * Get States by country
   * @param {string} country
   */
  countryOnChange(country) {
    //ajax to get states
    this.$.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_get_states",
        countryCode: country,
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: () => {
        //block form name="checkout"
        this.$("#fc-wrapper").block({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6
          }
        });
      },
      success: (response) => {
        //unblock
        this.$("#fc-wrapper").unblock();
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
        //set state options
        this.$('select[name="shipping_state"]').html(state_options);
        //clear select name terminal_custom_shipping_lga2
        let lga = this.$('select[name="shipping_city"]');
        //check if element exist
        if (lga.length > 0) {
          //clear
          this.$('select[name="shipping_city"]').html("");
        } else {
          //clear
          this.$("input[name='shipping_city']").val("");
        }
      },
      error: () => {
        //unblock
        this.$("#fc-wrapper").unblock();
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
  }

  /**
   * Get local governments by details
   * @param {string} country
   * @param {string} state
   */
  getLocalGovernments(country, state) {
    //ajax
    this.$.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_get_cities",
        countryCode: country,
        stateCode: state,
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: () => {
        //block form name="checkout"
        this.$("#fc-wrapper").block({
          message: null,
          overlayCSS: {
            background: "#fff",
            opacity: 0.6
          }
        });
      },
      success: (response) => {
        //unblock
        this.$("#fc-wrapper").unblock();
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
        this.passValuesToCities(response.cities);
      },
      error: (xhr, status, error) => {
        //unblock
        this.$("#fc-wrapper").unblock();
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

  /**
   * Pass values to cities
   * @param {array} cities
   * @param {string} selected
   */
  passValuesToCities(cities, selected = "") {
    let content = `
          <select name="shipping_city" id="shipping_city" class="terminal-custom-fluid-added-shipping-city" data-label="City" placeholder="City" onchange="terminalFluidCheckout.cityOnChange(this,event)">
              <option value="">Select City</option>
               ${cities
                 .map((city) => {
                   return `<option value="${city.name}" ${
                     selected == city.name ? 'selected="selected"' : ""
                   }>${city.name}</option>`;
                 })
                 .join("")}
          </select>
          `;
    //replace with #shipping_city
    this.$("#shipping_city").replaceWith(content);
    //animate from     margin-top: -10px; to margin-top: 0px;
    this.$("#shipping_city")
      .css({
        marginTop: "-10px"
      })
      .animate({ marginTop: 0 }, 1000);
  }

  /**
   *
   * @param {HTMLElement} elem
   */
  cityOnChange(elem) {
    //Check if shipping is enabled by woocommerce
    var terminal_delivery_html = this.isTerminalRunning();
    //check if terminal_delivery_html exist
    if (!terminal_delivery_html) {
      //do nothing
      return;
    }
    //get country value
    let country = this.$("select[name='shipping_country']");
    //check if country exists
    if (country.length > 0) {
      country = country.val();
    } else {
      country = this.$("input[name='shipping_country']").val();
    }
    //get state selected option value
    let state = this.$("select[name=shipping_state]").val();
    //get city selected option value
    let city = this.$(elem).val();
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
    let customer_details = this.getCustomerDetails();
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
    terminalFluidCheckout.getTerminalShippingRate(customer_details);
  }

  /**
   *
   * @returns object
   */
  getCustomerDetails() {
    //get customer details
    let customer_details = {};
    //check if element exist #shipping_first_name
    if (this.$("#shipping_first_name").length) {
      let firstName = this.$("#shipping_first_name").val();
      //add to customer_details
      customer_details.firstName = firstName;
    }
    //check if element exist #shipping_last_name
    if (this.$("#shipping_last_name").length) {
      let lastName = this.$("#shipping_last_name").val();
      //add to customer_details
      customer_details.lastName = lastName;
    }

    //check if element exist #shipping_phone
    if (this.$("#shipping_phone").length) {
      let phone = this.$("#shipping_phone").val();
      //add to customer_details
      customer_details.phone = phone;
    }
    //check if element exist #billing_email
    if (this.$("#billing_email").length) {
      let billing_email = this.$("#billing_email").val();
      //add to customer_details
      customer_details.email = billing_email;
    }
    //check if element exist #shipping_address_1
    if (this.$("#shipping_address_1").length) {
      let shipping_address_1 = this.$("#shipping_address_1").val();
      //add to customer_details
      customer_details.address = shipping_address_1;
    }
    //check if element exist #shipping_postcode
    if (this.$("#shipping_postcode").length) {
      let shipping_postcode = this.$("#shipping_postcode").val();
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
  }

  /**
   * Get terminal shipping rate
   * @param {object} customer_details
   */
  getTerminalShippingRate(customer_details) {
    //reset carrier data
    termianlDataParcel.clearCarrierData();
    //update woocommerce
    this.$(document.body).trigger("update_checkout");
    //get terminal countries
    let tm_countries = terminal_africa.terminal_africal_countries;
    //get country
    let countryCode = customer_details.country;
    //find country where isoCode is NG
    let tm_country = tm_countries.find(
      (country) => country.isoCode === countryCode
    );
    //get state
    let state = this.$("select[name='shipping_state'] option:selected").val();
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
    phonecode = phonecode.replace(/[- ]/g, "");
    //remove + and space and special characters form phone
    if (phone) {
      phone = phone.replace(/[-+()]/g, "");
      //append to phone
      phone = phonecode + phone;
    } else {
      //set phone to phonecode
      phone = "";
    }
    //get line_1
    let line_1 = customer_details.address;
    //get billing_postcode
    let billing_postcode = customer_details.postcode;
    //get stateText
    let stateText = this.$(
      "select[name='shipping_state'] option:selected"
    ).text();
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
    this.$.ajax({
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
      beforeSend: () => {
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
      success: (response) => {
        //Swal close
        Swal.close();
        //check response is 200
        if (response.code === 200) {
          //do something cool
          //clear .t-checkout-carriers-checkoutFluid
          this.$(".t-checkout-carriers-checkoutFluid").remove();
          let terminal_html = `
          <div class="t-checkout-carriers-checkoutFluid">
          <div class="t-checkout-carriers">
          `;
          //loop through response.data
          this.$.each(response.data, function (indexInArray, value) {
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
                <div class="t-checkout-single" onclick="terminalFluidCheckout.terminalSetShippingCrarrier(this, event)" data-carrier-name="${
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
          var terminal_delivery_html = this.$(
            "#shipping_method_0_terminal_delivery12"
          );
          //find parent li
          var terminal_delivery_li = terminal_delivery_html.parent();
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
  }

  /**
   * section | setshipping
   * terminalSetShippingCrarrier
   */
  terminalSetShippingCrarrier(elem, event) {
    //get terminal shipping input
    let terminalimage = this.isTerminalRunning(true);
    //check if terminal_image_prev is not empty
    if (terminalimage.length) {
      //check if terminal_image_prev is input type radio
      if (terminalimage.is("input[type='radio']")) {
        //check the input
        terminalimage.trigger("click");
      }
    }
    let carriername = this.$(elem).attr("data-carrier-name");
    let amount = this.$(elem).attr("data-amount");
    let duration = this.$(elem).attr("data-duration");
    let pickup = this.$(elem).attr("data-pickup");
    let email = this.$('input[name="billing_email"]').val();
    let rateid = this.$(elem).attr("data-rateid");
    let carrierlogo = this.$(elem).attr("data-image-url");
    //save to session
    this.$.ajax({
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
      beforeSend: () => {
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
      success: (response) => {
        //close swal
        Swal.close();
        //if response code 200
        if (response.code == 200) {
          //TODO add realtime update to terminal_carrier_logo
          //save carrier logo to session
          localStorage.setItem("terminal_carrier_logo", carrierlogo); //i stopped here
          //update woocommerce
          this.$(document.body).trigger("update_checkout");
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
      error: (response) => {
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
  }
}

//init
let terminalFluidCheckout = new FluidCheckoutTerminal();

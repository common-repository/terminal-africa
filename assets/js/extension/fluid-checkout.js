/**
 * Extension for native woocommerce checkout page
 */
class TerminalNativeWoocommerce {
  /**
   * Constructor
   */
  constructor() {
    this.$ = jQuery;
    //get terminal autoload merchant address
    this.terminal_autoload_merchant_address =
      terminal_africa_parcel.terminal_autoload_merchant_address;
    //load in 2 seconds
    setTimeout(() => {
      var length = Object.keys(this.terminal_autoload_merchant_address).length;
      //check if the this.terminal_autoload_merchant_address is not empty
      if (length) {
        //init
        this.init();
      }
    }, 3000);
  }

  /**
   * Init
   */
  init() {
    //set the country default based on the merchant autoload address
    this.setCountryDefaultSelection();
    //set the state default based on the state default
    this.setStateDefaultSelection();
    //set default zip code
    this.setDefaultZipCode();
    //initialize the default cities
    this.initDefaultCities();
  }

  /**
   * setCountryDefaultSelection
   */
  setCountryDefaultSelection() {
    //Get the country element
    let country = this.$("input[name='shipping_country']");
    //get the default country
    let defaultCountry = this.terminal_autoload_merchant_address["country"];
    // Set the value of the country element to the default country
    country.val(defaultCountry);
    // Trigger the Select2 event to update the styled select box
    country.trigger("change");
  }

  /**
   * Check if word is uppercase
   * @param {String} string
   * @return {Boolean}
   */
  isUpperCase(string) {
    return string.toUpperCase() === string;
  }

  /**
   * setStateDefaultSelection
   */
  setStateDefaultSelection() {
    //Get the state element
    let state = this.$("select[name='shipping_state']");
    //get the default state
    let defaultState = this.terminal_autoload_merchant_address["state"];
    //check if defaultState is mixed with uppercase and lowercase
    if (this.isUpperCase(defaultState)) {
      //set the default state
      state.val(defaultState);
    } else {
      // Find the option with a text matching the default state and set the value
      let matchingOption = state.find(`option:contains('${defaultState}')`);
      if (matchingOption.length) {
        state.val(matchingOption.val());
      }
    }
    // Trigger the Select2 event to update the styled select box
    state.trigger("change");
  }

  /**
   * Initialize the default cities
   */
  initDefaultCities() {
    //set the default state cities
    terminalFluidCheckout.passValuesToCities(
      this.terminal_autoload_merchant_address["cities"]
    );
    //set a delay initialisation to the dom selection due to dom element creation
    setTimeout(() => {
      //get the cities element
      let citiesElement = document.querySelector(
        "select[name='shipping_city']"
      );
      //get the default city
      let defaultCity = this.terminal_autoload_merchant_address["city"];
      // set the value of the cities element to the default city
      citiesElement.value = defaultCity;
      // Trigger the Select2 event to update the styled select box
      citiesElement.dispatchEvent(new Event("change"));
      /////////////// save the cities to local storage ///////////////////
      //stringify
      var cities = JSON.stringify(
        this.terminal_autoload_merchant_address["cities"]
      );
      //save to local storage this.terminal_autoload_merchant_address["cities"]
      localStorage.setItem("terminal_delivery_cities", cities);
    }, 1000);
  }

  /**
   * Set Default Zip Code
   */
  setDefaultZipCode() {
    //get the default
    let defaultZipCode = this.terminal_autoload_merchant_address["zip"];
    //get the element
    let zipCodeElement = this.$("#shipping_postcode");
    //set to local
    localStorage.setItem("terminal_default_zip_code", defaultZipCode);
    //set the default zip code
    zipCodeElement.val(defaultZipCode);
  }
}

/**
 * Initialise TerminalNativeWoocommerce
 */
new TerminalNativeWoocommerce();

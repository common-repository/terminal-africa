/**
 * Terminal Data Parcel
 * @since 1.10.3
 * @author Adeleye Ayodeji
 */
class TerminalDataParcel {
  /**
   * Constructor
   */
  constructor() {
    this.jquery = jQuery;
    this.createOrupdateParcel();
    //on add to cart
    this.listenToCart();
    //remove available carriers
    this.clearCarrierData();
  }

  /**
   * Create or update parcel
   * @since 1.10.3
   */
  createOrupdateParcel(updatBtn = false) {
    //set session storage terminal_africa_save_cart_itemcount
    sessionStorage.setItem("terminal_africa_save_cart_itemcount", "0");
    //check if update btn is true
    if (!updatBtn) {
      //check if cart is empty
      if (terminal_africa_parcel.is_cart_empty == "yes") {
        //do nothing
        return;
      }
    }
    //Check if product support shipment
    if (
      terminal_africa_parcel.terminal_check_checkout_product_for_shipping_support ==
      "false"
    ) {
      //do nothing
      return;
    }
    //Save cart item as parcel
    this.jquery.ajax({
      type: "POST",
      url: terminal_africa_parcel.ajax_url,
      data: {
        action: "terminal_africa_save_cart_item",
        nonce: terminal_africa_parcel.nonce
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
                  <img src="${terminal_africa_parcel.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
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
              //check if function exist
              if (typeof saveCartTerminalData === "function") {
                //try again
                saveCartTerminalData();
              }
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
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
      }
    });
  }

  /**
   * Listen to cart
   * @since 1.10.4
   */
  listenToCart() {
    //listen to added to cart
    this.jquery(document.body).on(
      "added_to_cart",
      (e, fragments, cart_hash) => {
        //trigger
        this.createOrupdateParcel(true);
      }
    );
  }

  /**
   * Clear carrier data
   * @since 1.10.4
   * @return void
   */
  clearCarrierData() {
    //clear local storage terminal_delivery_html
    localStorage.removeItem("terminal_delivery_html");
    //check if element exist
    if (this.jquery(".t-checkout-carriers").length) {
      //clear .t-checkout-carriers
      this.jquery(".t-checkout-carriers").remove();
    }
    //ajax
    this.jquery.ajax({
      type: "POST",
      url: terminal_africa_parcel.ajax_url,
      data: {
        action: "terminal_reset_carriers_data",
        nonce: terminal_africa_parcel.nonce
      },
      dataType: "json",
      success: (response) => {
        //update woocommerce
        this.jquery(document.body).trigger("update_checkout");
        // console.log(response);
        setTimeout(() => {
          this.jquery(document.body).trigger("update_checkout");
          // console.log("updated successfully");
        }, 3000);
      }
    });
  }
}

//init
let termianlDataParcel = new TerminalDataParcel();

//////////// Functions ////////////////////////////////////////////////////////////
let terminalFormatCurrency = function (amount) {
  //check if terminal_africa.multicurrency length is greater than 0
  if (terminal_africa.multicurrency.length > 0) {
    //init amount
    let initialAmount = terminal_africa.multicurrency[0];
    //other amount
    let otherAmount = terminal_africa.multicurrency[1];
    //add up
    let totalAmount = parseFloat(initialAmount) + parseFloat(otherAmount);
    //multiply amount
    let multipliedAmount = amount * totalAmount;
    //round up to whole number
    multipliedAmount = Math.round(multipliedAmount);
    //return amount
    return multipliedAmount;
  } else {
    //return amount
    return amount;
  }
};

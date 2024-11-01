jQuery(document).ready(function ($) {
  /**
   * Init payment object
   *
   */
  var terminal_africa_payment = {
    init: function () {
      this.payment_form();
      this.auto_load();
      //payment status
      this.payment_status();
    },
    //payment status
    payment_status: function () {
      //check if elem exist .terminal-africa-payment-status
      const terminalAfricaPaymentStatus = $(".terminal-africa-payment-status");

      //check if elem does not exist
      if (!terminalAfricaPaymentStatus) return;

      //send request to get payment status
      $.ajax({
        type: "POST",
        url: wc_terminal_africa_payment_params.ajax_url,
        data: {
          action: "terminal_africa_payment_status",
          order_id: wc_terminal_africa_payment_params.order_id,
          nonce: wc_terminal_africa_payment_params.nonce
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
          terminalAfricaPaymentStatus.innerHTML = response.data.status;
        },
        error: function (xhr, status, error) {
          //unblock ui
          terminalAfricaPaymentStatus.unblock();
          //update the dom
          terminalAfricaPaymentStatus.innerHTML =
            "Something went wrong: " + xhr.responseText;
        }
      });
    },
    //button event
    payment_form: function () {
      //check if elem exist .terminal_africa_payment_form_class
      const terminalAfricaPaymentForm = $(
        ".terminal_africa_payment_form_class"
      );

      //check if elem does not exist
      if (!terminalAfricaPaymentForm) return;

      $(terminalAfricaPaymentForm).on("submit", function (e) {
        e.preventDefault();
        //get the form element
        var form = $(this);
        //send ajax request
        $.ajax({
          type: "POST",
          url: wc_terminal_africa_payment_params.ajax_url,
          data: {
            action: "terminal_africa_payment_init",
            order_id: wc_terminal_africa_payment_params.order_id,
            nonce: wc_terminal_africa_payment_params.nonce
          },
          beforeSend: function () {
            //block ui
            $.blockUI({
              message: "<p>Processing Payment</p>",
              css: {
                border: "none",
                padding: "15px",
                backgroundColor: "#000",
                "-webkit-border-radius": "10px",
                "-moz-border-radius": "10px",
                opacity: 0.5,
                color: "#fff"
              }
            });
          },
          success: function (response) {
            if (response.success) {
              //block ui
              $.blockUI({
                message: "<p>Redirecting to payment page</p>",
                css: {
                  border: "none",
                  padding: "15px",
                  backgroundColor: "#000",
                  "-webkit-border-radius": "10px",
                  "-moz-border-radius": "10px",
                  opacity: 0.5,
                  color: "#fff"
                }
              });
              //redirect to payment page
              window.location.href = response.data.redirect_url;
            } else {
              //unblock ui
              $.unblockUI();
              //swal error
              Swal.fire({
                icon: "error",
                title: "Oops...",
                text: response.data.message,
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
          },
          error: function (xhr, status, error) {
            //unblock ui
            $.unblockUI();
            //swal error
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
    //trigger on page load
    auto_load: function () {
      //check if elem exist .terminal_africa_payment_form_class
      const terminalAfricaPaymentForm = $(
        ".terminal_africa_payment_form_class"
      );

      //check if elem does not exist
      if (!terminalAfricaPaymentForm) return;

      $(terminalAfricaPaymentForm).submit();
    }
  };

  //init payment object
  terminal_africa_payment.init();
});

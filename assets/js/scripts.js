/**
 * Terminal Africa | Admin Script
 * @package Terminal Africa
 * @since 1.0.0
 * @version 1.0.0
 */
jQuery(document).ready(function ($) {
  //auth
  $("#t_form").submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var public_key = form.find("input[name='public_key']").val();
    var secret_key = form.find("input[name='secret_key']").val();
    //confirm swal
    Swal.fire({
      //icon info
      icon: "info",
      title: "Action required!",
      text: "Terminal Africa will overwrite your existing shipping location to ensure accurate address information",
      //text css
      customClass: {
        title: "swal-title",
        text: "swal-text",
        content: "swal-content",
        confirmButton: "swal-confirm-button",
        cancelButton: "swal-cancel-button"
      },
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(246 146 32)",
      cancelButtonColor: "rgb(0 0 0)",
      //icon color
      iconColor: "rgb(246 146 32)",
      //swal footer
      footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `,
      confirmButtonText: "Yes, continue!"
    }).then((result) => {
      if (result.value) {
        //ajax
        $.ajax({
          type: "POST",
          url: terminal_africa.ajax_url,
          data: {
            action: "terminal_africa_auth",
            public_key: public_key,
            secret_key: secret_key,
            nonce: terminal_africa.nonce
          },
          dataType: "json",
          beforeSend: function () {
            // Swal loader
            Swal.fire({
              title: "Authenticating...",
              text: "Please wait...",
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
            //close loader
            Swal.close();
            //check response is 200
            if (response.code == 200) {
              //swal success
              Swal.fire({
                icon: "success",
                type: "success",
                title: "Success!",
                text: "Terminal Africa has been successfully authenticated",
                confirmButtonColor: "rgb(246 146 32)",
                cancelButtonColor: "rgb(0 0 0)",
                iconColor: "rgb(246 146 32)",
                footer: `
                  <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                  </div>
                `
              }).then((result) => {
                if (result.value) {
                  //check if terminal_africa.getting_started_url is none
                  if (terminal_africa.getting_started_url == "none") {
                    //do nothing
                  } else {
                    //show loading
                    Swal.fire({
                      title: "Redirecting...",
                      text: "Please wait...",
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
                    //redirect to getting started page
                    window.location.href = terminal_africa.getting_started_url;
                  }
                }
              });
            } else {
              //swal error
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
            }
          },
          error: function (xhr, status, error) {
            //close loader
            Swal.close();
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
      }
    });
  });

  //select2 template
  let formatState = (state) => {
    if (!state.id) {
      return state.text;
    }
    var $state = $(
      "<span>" + state.element.dataset.flag + " " + state.text + "</span>"
    );
    return $state;
  };

  /**
   * After country dom data loaded
   */
  let countriesDomData = () => {
    //init select2 .t-terminal-country
    $(".t-terminal-country").select2({
      placeholder: "Select country",
      allowClear: true,
      width: "100%",
      //select class
      dropdownCssClass: "t-form-control",
      //dropdown parent
      dropdownParent: $(".t-terminal-country").parent(),
      //template
      templateResult: formatState,
      templateSelection: formatState
    });
  };

  //init select2 .t-terminal-country
  $(".t-terminal-country-default-settings").select2({
    placeholder: "Select country",
    allowClear: true,
    width: "100%",
    //select class
    dropdownCssClass: "t-form-control",
    //dropdown parent
    dropdownParent: $(".t-terminal-country-default-settings").parent(),
    //template
    templateResult: formatState,
    templateSelection: formatState
  });

  //event onchange
  $(".t-terminal-country").change(function (e) {
    //prevent default
    e.preventDefault();
    //get value
    var country = $(this).val();
    //check country
    if (country) {
      //ajax
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
          // Swal loader
          Swal.fire({
            title: "Processing...",
            text: "Please wait...",
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
          //close loader
          Swal.close();
          //check response is 200
          if (response.code == 200) {
            //destroy select2
            $(".t-terminal-state").select2("destroy");
            //remove all options
            $(".t-terminal-state").find("option").remove();
            //append options
            $(".t-terminal-state").append(
              "<option value=''>Select State</option>"
            );
            //loop
            $.each(response.states, function (key, value) {
              //append options
              $(".t-terminal-state").append(
                "<option value='" +
                  value.name +
                  "' data-statecode='" +
                  value.isoCode +
                  "'>" +
                  value.name +
                  "</option>"
              );
            });
            //init select2 .t-terminal-state
            $(".t-terminal-state").select2({
              placeholder: "Select state",
              allowClear: true,
              width: "100%",
              //select class
              dropdownCssClass: "t-form-control",
              //dropdown parent
              dropdownParent: $(".t-terminal-state").parent()
            });
          } else {
            //destroy select2
            $(".t-terminal-state").select2("destroy");
            //remove all options
            $(".t-terminal-state").find("option").remove();
            //append options
            $(".t-terminal-state").append(
              "<option value=''>Select State</option>"
            );
            //init select2 .t-terminal-state
            $(".t-terminal-state").select2({
              placeholder: "Select state",
              allowClear: true,
              width: "100%",
              //select class
              dropdownCssClass: "t-form-control",
              //dropdown parent
              dropdownParent: $(".t-terminal-state").parent()
            });
            //swal error
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
          }
        },
        error: function (xhr, status, error) {
          //close loader
          Swal.close();
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
    }
  });

  //init select2 .t-terminal-state
  $(".t-terminal-state").select2({
    placeholder: "Select Sate",
    allowClear: true,
    width: "100%",
    //select class
    dropdownCssClass: "t-form-control",
    //dropdown parent
    dropdownParent: $(".t-terminal-state").parent()
  });

  //event onchange
  $(".t-terminal-state").change(function (e) {
    //prevent default
    e.preventDefault();
    //get value
    var state = $(this).find("option:selected").data("statecode");
    var country = $(".t-terminal-country").val();
    //check if country is selected
    if (!country) {
      //swal error
      Swal.fire({
        icon: "error",

        title: "Oops...",
        text: "Please select country first!",
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
    //check state
    if (state && country) {
      //ajax
      $.ajax({
        type: "GET",
        url: terminal_africa.ajax_url,
        data: {
          action: "terminal_africa_get_cities",
          stateCode: state,
          countryCode: country,
          nonce: terminal_africa.nonce
        },
        dataType: "json",
        beforeSend: function () {
          // Swal loader
          Swal.fire({
            title: "Processing...",
            text: "Please wait...",
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
          //close loader
          Swal.close();
          //check response is 200
          if (response.code == 200) {
            //destroy select2
            $(".t-terminal-city").select2("destroy");
            //remove all options
            $(".t-terminal-city").find("option").remove();
            //append options
            $(".t-terminal-city").append(
              "<option value=''>Select City</option>"
            );
            //loop
            $.each(response.cities, function (key, value) {
              //append options
              $(".t-terminal-city").append(
                "<option value='" + value.name + "'>" + value.name + "</option>"
              );
            });
            //init select2 .t-terminal-city
            $(".t-terminal-city").select2({
              placeholder: "Select city",
              allowClear: true,
              width: "100%",
              //select class
              dropdownCssClass: "t-form-control",
              //dropdown parent
              dropdownParent: $(".t-terminal-city").parent()
            });
          } else {
            //destroy select2
            $(".t-terminal-city").select2("destroy");
            //remove all options
            $(".t-terminal-city").find("option").remove();
            //append options
            $(".t-terminal-city").append(
              "<option value=''>Select City</option>"
            );
            //init select2 .t-terminal-city
            $(".t-terminal-city").select2({
              placeholder: "Select city",
              allowClear: true,
              width: "100%",
              //select class
              dropdownCssClass: "t-form-control",
              //dropdown parent
              dropdownParent: $(".t-terminal-city").parent()
            });
            //swal error
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
          }
        },
        error: function (xhr, status, error) {
          //close loader
          Swal.close();
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
    } else {
      //destroy select2
      $(".t-terminal-city").select2("destroy");
      //remove all options
      $(".t-terminal-city").find("option").remove();
      //append options
      $(".t-terminal-city").append("<option value=''>Select City</option>");
      //init select2 .t-terminal-city
      $(".t-terminal-city").select2({
        placeholder: "Select city",
        allowClear: true,
        width: "100%",
        //select class
        dropdownCssClass: "t-form-control",
        //dropdown parent
        dropdownParent: $(".t-terminal-city").parent()
      });
      //log
      console.log("Please select state first!", state, country);
    }
  });

  //init select2 .t-terminal-city
  $(".t-terminal-city").select2({
    placeholder: "Select city",
    allowClear: true,
    width: "100%",
    //select class
    dropdownCssClass: "t-form-control",
    //dropdown parent
    dropdownParent: $(".t-terminal-city").parent()
  });

  /**
   * each .t-sign-out
   *
   */
  $(".t-sign-out").each(function (index, element) {
    $(this).on("click", function (e) {
      e.preventDefault();
      //swal confirm
      Swal.fire({
        title: "Are you sure?",
        text: "You want to sign out?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "rgb(246 146 32)",
        cancelButtonColor: "rgb(0 0 0)",
        confirmButtonText: "Yes, sign out!",
        cancelButtonText: "No, cancel!",
        //footer
        footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
      }).then((result) => {
        //check result
        if (result.value) {
          //clear terminal_delivery_html
          localStorage.removeItem("terminal_delivery_html");
          //ajax
          $.ajax({
            type: "GET",
            url: terminal_africa.ajax_url,
            data: {
              action: "terminal_africa_sign_out",
              nonce: terminal_africa.nonce
            },
            dataType: "json",
            beforeSend: function () {
              // Swal loader
              Swal.fire({
                title: "Signing out...",
                text: "Please wait...",
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
              //close loader
              Swal.close();
              //check response is 200
              if (response.code == 200) {
                //swal
                Swal.fire({
                  icon: "success",
                  title: "Success",
                  text: response.message,
                  confirmButtonColor: "rgb(246 146 32)",
                  cancelButtonColor: "rgb(0 0 0)",
                  //footer
                  footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
                }).then(function () {
                  //reload
                  window.location.href = response.redirect_url;
                });
              } else {
                //swal error
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
              }
            },
            error: function (xhr, status, error) {
              //close loader
              Swal.close();
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
        }
      });
    });
  });

  //t-dashboard-intro-list-item
  $(".t-dashboard-intro-list-item").each(function (index, element) {
    $(this).click(function (e) {
      e.preventDefault();
      //remove all active .t-products-active
      $(".t-dashboard-intro-list-item").removeClass("t-products-active");
      //add active
      $(this).addClass("t-products-active");
      //get data box
      let data_box = $(this).attr("data-box");
      //check if cuurent width is min 600px
      if ($(window).width() <= 700) {
        //hide .t-intro-list
        $(".t-intro-list").hide();
        //show .t-intro-content-block
        $(".t-intro-content-block").show();
      } else {
        //hide .t-intro-list
        $(".t-intro-list").show();
        //show .t-intro-content-block
        $(".t-intro-content-block").show();
      }
      //if data box is t-add-products
      if (data_box == "t-add-products") {
        //hide .t-get-support
        $(".t-get-support").hide();
        //show .t-add-products
        $(".t-add-products").show();
      } else {
        //hide .t-add-products
        $(".t-add-products").hide();
        //show .t-get-support
        $(".t-get-support").show();
      }
    });
  });

  //on window resize
  $(window).resize(function () {
    //check if cuurent width is min 600px
    if ($(window).width() <= 700) {
      //hide .t-intro-list
      $(".t-intro-list").hide();
      //show .t-intro-content-block
      $(".t-intro-content-block").show();
    } else {
      //hide .t-intro-list
      $(".t-intro-list").show();
      //show .t-intro-content-block
      $(".t-intro-content-block").show();
    }
  });

  //click mobile-resources-back-link-block
  $(".mobile-resources-back-link-block").click(function (e) {
    e.preventDefault();
    //hide .t-intro-content-block
    $(".t-intro-content-block").hide();
    //show .t-intro-list
    $(".t-intro-list").show();
  });

  //enableTerminal
  $("#enableTerminal").click(function (e) {
    //prevent default
    e.preventDefault();
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_enable_terminal",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: function () {
        // Swal loader
        Swal.fire({
          title: "Enabling...",
          text: "Please wait...",
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
        //close loader
        Swal.close();
        //check response is 200
        if (response.code == 200) {
          //swal
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.message,
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            //footer
            footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
          }).then(function () {
            //reload
            window.location.reload();
          });
        } else {
          //swal error
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
        }
      },
      error: function (xhr, status, error) {
        //close loader
        Swal.close();
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
});

let updateData = (elem, e, element_id) => {
  //prevent default
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //get the value
    let value = $(elem).val();
    //get the element
    let element = $(`#${element_id}`);
    //update the value
    element.text(value);
  });
};

let changeTerminalCarrier = (elem, e) => {
  //prevent default
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //process the terminal rates
    var shipment_id = $(elem).data("shipment_id");
    var order_id = $(elem).data("order-id");
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_process_terminal_rates_customer",
        nonce: terminal_africa.nonce,
        shipment_id: shipment_id
      },
      dataType: "json",
      beforeSend: function () {
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
                <div class="t-checkout-single" onclick="terminalSetShippingCrarrier2(this, event)" data-carrier-name="${value.carrier_name}" data-amount="${default_amount}" data-duration="${value.delivery_time}" data-pickup="${value.pickup_time}" data-order-id="${order_id}" data-rateid="${value.rate_id}" data-image-url="${value.carrier_logo}">
                <label for="shipping">
                <div style="display: flex;justify-content: start;align-items: center;    padding: 10px;    padding-bottom: 0px;">
                  <img class="Terminal-carrier-delivery-logo" alt="${value.carrier_name}" title="${value.carrier_name}" style="width: auto;height: auto;margin-right: 10px;    max-width: 30px;" src="${value.carrier_logo}">
                  <b style=""> 
                        <span style="font-weight: bolder;">${value.carrier_name}</span> - ${amount}
                    </b>
                </div>
                <div>
                  <p>
                    <span class="t-carrier-desc">${value.carrier_rate_description}</span>
                    <br />
                    <span class="t-delivery-time">Pickup: ${value.pickup_time}</span>
                    <br />
                    <span class="t-delivery-time">Delivery: ${value.delivery_time}</span>
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
          var terminal_delivery_html = $("#t_carriers_location");
          //append to li
          terminal_delivery_html.html(terminal_html);
        } else {
          //swal error
          Swal.fire({
            icon: "error",
            title: "Oops...",
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
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
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
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
};

let terminalSetShippingCrarrier2 = (elem, e) => {
  //prevent default
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //get the data
    var carrier_name = $(elem).data("carrier-name");
    var amount = $(elem).data("amount");
    var duration = $(elem).data("duration");
    var pickup = $(elem).data("pickup");
    var rateid = $(elem).data("rateid");
    var order_id = $(elem).data("order-id");
    let carrierlogo = $(elem).attr("data-image-url");
    //do ajax
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_apply_terminal_rates_customer",
        nonce: terminal_africa.nonce,
        carrier_name: carrier_name,
        amount: amount,
        duration: duration,
        pickup: pickup,
        rateid: rateid,
        order_id: order_id,
        carrierlogo: carrierlogo
      },
      dataType: "json",
      beforeSend: function () {
        // Swal loader
        Swal.fire({
          title: "Please wait...",
          text: "Applying Shipping Rates",
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
          //swal success
          Swal.fire({
            icon: "success",
            title: "Success",
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            text: response.message,
            footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
          }).then(() => {
            //reload page
            window.location.href = response.url;
          });
        } else {
          //swal error
          Swal.fire({
            icon: "error",
            title: "Oops...",
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
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
        //close loader
        Swal.close();
        //swal error
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong!: " + xhr.responseText,
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
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

/**
 * Validate Terminal Shipment
 * @param {string} rateid
 */
let validateTerminalShipment = (rateid, order_id, shipment_id) => {
  jQuery(document).ready(function ($) {
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_validate_terminal_shipment",
        nonce: terminal_africa.nonce,
        rateid: rateid,
        order_id,
        shipment_id
      },
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "Please wait...",
          text: "Validating Terminal Shipment",
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
        //close loader
        Swal.close();
        //check response code is 200
        switch (response.code) {
          case 200:
            //get dropoff locations
            var dropoff_locations = response.data;
            //show dropoff location pop up as html swal
            Swal.fire({
              title: "Dropoff Location Required",
              html: `
                <div>
                  <p>Please select a dropoff location for your shipment.</p>
                  <select id="dropoff-location" class="swal2-input">
                    <option value="">Select a dropoff location</option>
                    ${dropoff_locations
                      .map((location) => {
                        return `<option value="${location.dropoff_id}">${location.address}</option>`;
                      })
                      .join("")}
                  </select>
                </div>
              `,
              confirmButtonText: "Submit",
              showLoaderOnConfirm: true,
              allowOutsideClick: false,
              allowEscapeKey: false,
              allowEnterKey: false,
              showConfirmButton: true,
              showCancelButton: true,
              cancelButtonText: "Cancel",
              preConfirm: () => {
                const dropoffLocation =
                  Swal.getPopup().querySelector("#dropoff-location").value;
                if (!dropoffLocation) {
                  Swal.showValidationMessage(
                    `Please select a dropoff location`
                  );
                }
                return { dropoffLocation: dropoffLocation };
              }
            }).then((result) => {
              if (result.isConfirmed) {
                // arrange delivery now with result.value.dropoffLocation
                arrangeTerminalDeliveryNow(
                  rateid,
                  order_id,
                  shipment_id,
                  result.value.dropoffLocation
                );
              }
            });
            break;
          case 402:
            //arrange delivery now
            arrangeTerminalDeliveryNow(rateid, order_id, shipment_id);
            break;
          case 400:
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
            break;
        }
      },
      error: function (xhr, status, error) {
        //close loader
        Swal.close();
        //swal error
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong!: " + xhr.responseText,
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
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

/**
 * Arrange Terminal Delivery Now
 * @param {string} rateid
 * @param {string} order_id
 * @param {string} shipment_id
 * @param {string} dropoff_id
 */
let arrangeTerminalDeliveryNow = (
  rateid,
  order_id,
  shipment_id,
  dropoff_id = "none"
) => {
  try {
    jQuery(document).ready(function ($) {
      //ajax
      $.ajax({
        type: "POST",
        url: terminal_africa.ajax_url,
        data: {
          action: "terminal_africa_arrange_terminal_delivery",
          nonce: terminal_africa.nonce,
          order_id: order_id,
          rateid: rateid,
          shipment_id: shipment_id,
          dropoff_id: dropoff_id
        },
        beforeSend: function () {
          // Swal loader
          Swal.fire({
            title: "Please wait...",
            text: "Arranging Delivery",
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
        dataType: "json",
        success: function (response) {
          //Swal close
          Swal.close();
          //check response is 200
          if (response.code == 200) {
            //swal success
            Swal.fire({
              icon: "success",
              title: "Success",
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              text: response.message,
              footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
            }).then(() => {
              //reload page
              window.location.reload();
            });
          } else {
            if (response.code === 400) {
              //swal error
              Swal.fire({
                icon: "error",
                title: "Insufficient funds",
                confirmButtonColor: "rgb(246 146 32)",
                cancelButtonColor: "rgb(0 0 0)",
                //confirm button
                confirmButtonText: "Add funds",
                showCancelButton: true,
                cancelButtonText: "Cancel, I'll do it later",
                text: response.message,
                footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
              }).then((result) => {
                if (result.value) {
                  //redirect to add funds page
                  window.location.href = terminal_africa.wallet_url;
                  return;
                }
              });
            } else {
              //swal error
              Swal.fire({
                icon: "error",
                title: "Oops...",
                confirmButtonColor: "rgb(246 146 32)",
                cancelButtonColor: "rgb(0 0 0)",
                text: response.message,
                confirmButtonText: "Try again",
                footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
              });
            }
          }
        },
        error: function (xhr, status, error) {
          //close loader
          Swal.close();
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
  } catch (error) {
    console.log(error);
  }
};

/**
 * Arrange Terminal Delivery
 * @param {object} elem
 * @param {object} e
 */
let arrangeTerminalDelivery = (elem, e) => {
  e.preventDefault();
  jQuery(document).ready(function ($) {
    let rateid = $(elem).data("rate-id");
    let order_id = $(elem).data("order-id");
    let shipment_id = $(elem).data("shipment_id");
    //Swal confirm
    Swal.fire({
      title: "Are you sure?",
      text: "You want to arrange delivery for this order!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(246 146 32)",
      cancelButtonColor: "rgb(0 0 0)",
      confirmButtonText: "Yes, arrange it!"
    }).then((result) => {
      if (result.value) {
        //validate terminal shipment
        validateTerminalShipment(rateid, order_id, shipment_id);
      }
    });
  });
};

//gotoTerminalPage
let gotoTerminalPage = (elem, page) => {
  jQuery(document).ready(function ($) {
    let page1 = ["t-wallet-home", "t-wallet-topup"];
    let page2 = ["t-amount-input", "t-confirm-bank"];
    //if page is in page1
    if (page1.includes(page)) {
      //remove active class from all page1
      $.each(page1, function (i, v) {
        $(`.${v}`).hide();
      });
      //check if session storage is set
      if (sessionStorage.getItem("bank") === "true") {
        //remove active class from all page2
        $.each(page2, function (i, v) {
          $(`.${v}`).hide();
        });
        //add active class to page
        $(`.t-wallet-topup, .t-amount-input`).show();
        //clear session storage
        sessionStorage.clear();
        // console.log("session storage cleared");
        return;
      }
      //add active class to page
      $(`.${page}`).show();
    }
    //if page is in page2
    if (page2.includes(page)) {
      //check if session storage is
      if (sessionStorage.getItem("amount") !== "true") {
        //Swal
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Please enter amount first!",
          confirmButtonColor: "rgb(246 146 32)",
          cancelButtonColor: "rgb(0 0 0)",
          footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
        });
        return;
      }
      //remove active class from all page2
      $.each(page2, function (i, v) {
        $(`.${v}`).hide();
      });
      //add active class to page
      $(`.${page}`).show();
      //check if page is t-confirm-bank then session storage
      if (page === "t-confirm-bank") {
        sessionStorage.setItem("bank", "true");
        // console.log("session storage set");
      } else {
        //remove session storage
        sessionStorage.removeItem("bank");
        // console.log("session storage removed");
      }
    }
  });
};

//.t-top-up-amount-input keyup and change
jQuery(document).ready(function ($) {
  $(document).on(
    "keyup change keydown blur",
    ".t-top-up-amount-input",
    function () {
      let amount = $(this).val();
      //Strip all non-numeric characters from string
      amount = amount.replace(/\D/g, "");
      //check if amount is empty
      if (amount === "") {
        //disable button
        $(".t-top-up-amount-btn").attr("disabled", true);
        //remove session storage
        sessionStorage.removeItem("amount");
        return;
      }
      //enable button
      $(".t-top-up-amount-btn").attr("disabled", false);
      //session storage
      sessionStorage.setItem("amount", "true");
      //get old balance
      let oldBalance = $(".t-NGN-balance");
      let amount2 = amount;
      //check if element exist
      if (oldBalance.length) {
        //get data balance
        let dataBalance = oldBalance.data("balance");
        //convert to number
        let balance = Number(dataBalance);
        //convert amount to number
        amount = Number(amount);
        //add amount to balance
        amount = balance + amount;
      }
      //format to price format
      let formattedAmount = new Intl.NumberFormat("en-NG", {
        style: "currency",
        currency: "NGN"
      }).format(amount);
      //format amount2
      let formattedAmount2 = new Intl.NumberFormat("en-NG", {
        style: "currency",
        currency: "NGN"
      }).format(amount2);
      //set amount to display
      $(".t-balance-sub-text:first").html(
        `Balance after topup - ${formattedAmount}`
      );
      $(".t-top-up-amount").html(formattedAmount2);
      //add comma to amount
      var tempNumber = $(this).val().replace(/,/gi, "");
      var commaSeparatedNumber = tempNumber.split(/(?=(?:\d{3})+$)/).join(",");
      $(this).val(commaSeparatedNumber);
    }
  );
});

let confirmTerminalTransfer = (elem, e) => {
  //prevent default
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //Swal success
    Swal.fire({
      icon: "success",
      title: "Top Up Completed!",
      confirmButtonColor: "rgb(246 146 32)",
      //confirm button text
      confirmButtonText: "Continue",
      text: "You should receive confirmation once your transfer is confirmed.",
      footer: `
        <div>
          <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `
    }).then(() => {
      //reload page
      window.location.href = terminal_africa.wallet_home;
    });
  });
};

//refreshTerminalWallet
let refreshTerminalWallet = () => {
  jQuery(document).ready(function ($) {
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "refresh_terminal_wallet",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
      }
    });
  });
};

//refreshTerminalRate
let refreshTerminalRate = (rate_id) => {
  jQuery(document).ready(function ($) {
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "refresh_terminal_rate_data",
        nonce: terminal_africa.nonce,
        rate_id: rate_id
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
      }
    });
  });
};

//refreshTerminalCarriers
let refreshTerminalCarriers = () => {
  jQuery(document).ready(function ($) {
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "refresh_terminal_carriers_data",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
      }
    });
  });
};

//cancelTerminalShipment
let cancelTerminalShipment = (elem, e) => {
  e.preventDefault();
  jQuery(document).ready(function ($) {
    //Swal confirm
    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(246 146 32)",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, cancel it!"
    }).then((result) => {
      if (result.value) {
        //ajax
        $.ajax({
          type: "GET",
          url: terminal_africa.ajax_url,
          data: {
            action: "cancel_terminal_shipment",
            nonce: terminal_africa.nonce,
            shipment_id: elem.dataset.shipment_id,
            order_id: elem.dataset.order_id,
            rate_id: elem.dataset.rate_id
          },
          dataType: "json",
          beforeSend: function () {
            //loader
            Swal.fire({
              title: "Cancelling Shipment...",
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
            // console.log(response);
            //check if response is success
            if (response.code == 200) {
              //Swal success
              Swal.fire({
                icon: "success",
                title: "Shipment Cancelled!",
                confirmButtonColor: "rgb(246 146 32)",
                //confirm button text
                confirmButtonText: "Continue",
                text: "Your shipment has been cancelled.",
                footer: `
                  <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                  </div>
                `
              }).then(() => {
                //reload page
                window.location.reload();
              });
            } else {
              //Swal error
              Swal.fire({
                icon: "error",
                title: "Oops...",
                confirmButtonColor: "rgb(246 146 32)",
                //confirm button text
                confirmButtonText: "Continue",
                text: response.message,
                footer: `
                  <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                  </div>
                `
              });
            }
          }
        });
      }
    });
  });
};

let getShipmentStatus = (shipment_id, order_id, rate_id) => {
  jQuery(document).ready(function ($) {
    //ajax
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "get_terminal_shipment_status",
        nonce: terminal_africa.nonce,
        shipment_id,
        order_id,
        rate_id
      },
      dataType: "json",
      beforeSend: () => {
        // Swal loader
        Swal.fire({
          title: "Please wait...",
          text: "We are fetching your shipment status",
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
        //disable all input #t-form-submit
        $("#t-form-submit")
          .find("input, button, select, textarea")
          .attr("disabled", "disabled");
        //add readonly to all input
        $("#t-form-submit")
          .find("input, button, select, textarea")
          .attr("readonly", "readonly");
      },
      success: function (response) {
        //close   Swal loader
        Swal.close();
        //check if response code is 200
        if (response.code === 200) {
          //check for cancelled shipment
          let cancellation_request =
            response.shipment_info.cancellation_request;
          // PENDING CANCELLATION
          //update #terminal_shipment_status html
          if (cancellation_request) {
            $("#terminal_shipment_status").html("PENDING CANCELLATION");
          } else {
            $("#terminal_shipment_status").html(response.data);
          }
          //check if status is draft
          if (response.data === "draft") {
            //enable all input #t-form-submit
            $("#t-form-submit")
              .find("input, button, select, textarea")
              .removeAttr("disabled");
            //remove readonly to all input
            $("#t-form-submit")
              .find("input, button, select, textarea")
              .removeAttr("readonly");
          } else {
            if (!cancellation_request) {
              let shipping_info = response.shipment_info.extras;
              let address_from = response.shipment_info.address_from.country;
              let address_to = response.shipment_info.address_to.country;
              //add template for info
              let template = `
              <div class="t-space"></div>
              ${
                shipping_info.shipping_label_url != null ||
                shipping_info.shipping_label_url != undefined ||
                shipping_info.shipping_label_url != "" ||
                shipping_info.shipping_label_url != "null" ||
                shipping_info.shipping_label_url != "undefined"
                  ? `
              <p>
                  <b>Shipping Label:</b> <a href="${shipping_info.shipping_label_url}" class="t-shipment-info-link" target="_blank">View Label</a>
              </p>
              `
                  : ``
              }
                    <p>
                        <b>Tracking Number:</b> <b>${
                          shipping_info.tracking_number
                        }</b>
                    </p>
                    <p>
                        <b>Tracking Link:</b> <a href="${
                          terminal_africa.tracking_url + shipment_id
                        }" class="t-shipment-info-link" target="_blank">Track Shipment</a>
                    </p>

                    ${
                      address_from != address_to
                        ? `
                        <br>
                    <p>
                    <b>Commercial Invoice:</b> <a href="${shipping_info.commercial_invoice_url}" class="t-shipment-info-link" target="_blank">View Invoice</a>
                    </p>
                    <p>
                    <b>Carrier Tracking:</b> <a href="${shipping_info.carrier_tracking_url}" class="t-shipment-info-link" target="_blank">View Tracking</a>
                    </p>
                    `
                        : ``
                    }
                    
                    <div class="t-space"></div>
                `;
              //append before #t_carriers_location
              $("#t_carriers_location").before(template);
            }
          }
          //load button
          $("#t_carriers_location").html(response.button);
        } else {
          //Swal
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: response.message,
            confirmButtonColor: "rgb(246 146 32)",
            //confirm button text
            confirmButtonText: "Continue",
            footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
          }).then(() => {
            //enable all input #t-form-submit
            $("#t-form-submit")
              .find("input, button, select, textarea")
              .removeAttr("disabled");
            //remove readonly to all input
            $("#t-form-submit")
              .find("input, button, select, textarea")
              .removeAttr("readonly");
          });
        }
      }
    });
  });
};

//check if page match admin.php?page=terminal-africa-wallet
var currentpageurl = window.location.href;
if (currentpageurl.includes("admin.php?page=terminal-africa-wallet")) {
  //refresh terminal wallet
  refreshTerminalWallet();
  //check if tab is in url
  if (currentpageurl.includes("tab=")) {
    //trigger click .t-top-up-landing
    jQuery(document).ready(function ($) {
      $(".t-top-up-landing").trigger("click");
    });
  }
}
//check if page match admin.php?page=terminal-africa and has rate_id
if (currentpageurl.includes("admin.php?page=terminal-africa")) {
  //check if rate_id is in url
  if (currentpageurl.includes("rate_id=")) {
    //get rate id
    let rate_id = currentpageurl.split("rate_id=")[1];
    //check if & is in rate id
    if (rate_id.includes("&")) {
      //split rate id
      rate_id = rate_id.split("&")[0];
    }
    //get shipment status
    let shipment_id = currentpageurl.split("id=")[1];
    //check if & is in id
    if (shipment_id.includes("&")) {
      //split id
      shipment_id = shipment_id.split("&")[0];
    }
    //order_id
    let order_id = currentpageurl.split("order_id=")[1];
    //check if & is in order_id
    if (order_id.includes("&")) {
      //split order_id
      order_id = order_id.split("&")[0];
    }
    //if shipment id is not undefined
    if (
      shipment_id !== undefined &&
      shipment_id !== "" &&
      order_id !== undefined &&
      order_id !== "" &&
      rate_id !== undefined &&
      rate_id !== ""
    ) {
      //get shipment status
      // getShipmentStatus(shipment_id, order_id, rate_id);
      //refresh terminal rate
      refreshTerminalRate(rate_id);
    }
  }
}

//check if page is admin.php?page=terminal-africa-carriers
if (currentpageurl.includes("admin.php?page=terminal-africa-carriers")) {
  refreshTerminalCarriers();
}
//getSelectedDataTerminal
let getSelectedDataTerminal = () => {
  let terminalEnabledCarriers = [];
  let terminalDisabledCarriers = [];
  //get all element .t-carrier-region-listing-block check if input is check
  jQuery(document).ready(function ($) {
    //get all element .t-carrier-region-listing-block
    let elements = $(".t-carrier-region-listing-block");
    //check if element exist
    if (elements.length) {
      //loop through elements
      $.each(elements, function (i, v) {
        //get input
        let input = $(v).find("input");
        let ppp = $(v);
        let carrier_id = input.data("carrier-id");
        let domestic = ppp.data("domestic");
        //check if domestic is empty
        if (
          domestic === "" ||
          domestic === undefined ||
          domestic === null ||
          domestic === "null" ||
          domestic === "undefined" ||
          carrier_id === "undefined" ||
          carrier_id === undefined ||
          carrier_id === null ||
          carrier_id === "null"
        ) {
          //skip
          return;
        }
        let international = ppp.data("international");
        let regional = ppp.data("regional");
        //create carrier object
        let carrierObj = {
          id: carrier_id,
          domestic,
          international,
          regional
        };
        //check if input is checked
        if (input.is(":checked")) {
          //push to terminalEnabledCarriers
          terminalEnabledCarriers.push(carrierObj);
        } else {
          //push to terminalDisabledCarriers
          terminalDisabledCarriers.push(carrierObj);
        }
      });
    }
  });

  //return object
  return {
    terminalEnabledCarriers,
    terminalDisabledCarriers
  };
};

//.save-carrier-settings
jQuery(document).ready(function ($) {
  //click
  $(".save-carrier-settings").on("click", function (e) {
    //prevent default
    e.preventDefault();
    //get selected data
    let selectedData = getSelectedDataTerminal();
    //use Promise
    const myPromise = new Promise((resolve, reject) => {
      setTimeout(() => {
        return resolve(selectedData);
      }, 300);
    });
    //then
    myPromise.then((selectedData) => {
      //ajax
      $.ajax({
        type: "POST",
        url: terminal_africa.ajax_url,
        data: {
          action: "save_terminal_carrier_settings",
          nonce: terminal_africa.nonce,
          terminalEnabledCarriers: selectedData.terminalEnabledCarriers,
          terminalDisabledCarriers: selectedData.terminalDisabledCarriers
        },
        dataType: "json",
        beforeSend: () => {
          // Swal loader
          Swal.fire({
            title: "Please wait...",
            text: "Saving settings",
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
          console.log(response);
          if (response.code == 200) {
            //Swal success
            Swal.fire({
              icon: "success",
              title: "Settings Saved!",
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              //confirm button text
              confirmButtonText: "Continue",
              footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
            });
          } else {
            //Swal error
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: response.message,
              confirmButtonColor: "rgb(246 146 32)",
              cancelButtonColor: "rgb(0 0 0)",
              footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
            });
          }
        },
        error: function (xhr, status, error) {
          //close loader
          Swal.close();
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
  });
});

//load terminal_packaging
let loadTerminalPackaging = () => {
  jQuery(document).ready(function ($) {
    $.ajax({
      type: "GET",
      url: terminal_africa.ajax_url,
      data: {
        action: "get_terminal_packaging",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
      }
    });
  });
};

//duplicateTerminalShipment
let duplicateTerminalShipment = (elem, e) => {
  //do something
  e.preventDefault();
  //alert coming soon
  Swal.fire({
    icon: "info",
    title: "Coming Soon",
    text: "This feature is coming soon!",
    confirmButtonColor: "rgb(246 146 32)",
    cancelButtonColor: "rgb(0 0 0)",
    footer: `
    <div>
      <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
    </div>
  `
  });
};

//check if terminal_africa.packaging_id is no
if (terminal_africa.packaging_id == "no") {
  loadTerminalPackaging();
}

//.t-carrier-switch
jQuery(document).ready(function ($) {
  //listen to #terminal_custom_price_mark_up on focus out
  $("#terminal_custom_price_mark_up").on("focusout", function (e) {
    //get value
    let value = $(this).val();
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "save_terminal_custom_price_mark_up",
        nonce: terminal_africa.nonce,
        percentage: value
      },
      dataType: "json",
      beforeSend: () => {
        //izitoast
        iziToast.show({
          theme: "dark",
          title: "Saving custom price mark up",
          position: "topRight",
          progressBarColor: "rgb(246 146 32)",
          transitionIn: "fadeInDown",
          timeout: false
        });
      },
      success: function (response) {
        //close izitoast
        iziToast.destroy();
        if (response.code == 200) {
          //izitoast
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        } else {
          //izitoast
          iziToast.error({
            theme: "dark",
            title: "Error",
            message: response.message,
            position: "topCenter",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  });

  //each element
  $(".t-carrier-switch").each(function (i, v) {
    //find input checkbox and change event
    $(v)
      .find("input")
      .on("change", function (e) {
        //get parent
        let parent = $(this).parents(".t-carrier-region-listing-block");
        //carrier id
        let carrier_id = $(this).data("carrier-id");
        //domestic
        let domestic = parent.data("domestic");
        //international
        let international = parent.data("international");
        //regional
        let regional = parent.data("regional");
        //create carrier object
        let carrierObj = {
          id: carrier_id,
          domestic,
          international,
          regional
        };
        //check if all are not empty
        if (
          carrier_id !== "" &&
          carrier_id !== undefined &&
          carrier_id !== null &&
          carrier_id !== "null" &&
          carrier_id !== "undefined" &&
          domestic !== "" &&
          domestic !== undefined &&
          domestic !== null &&
          domestic !== "null" &&
          domestic !== "undefined" &&
          international !== "" &&
          international !== undefined &&
          international !== null &&
          international !== "null" &&
          international !== "undefined" &&
          regional !== "" &&
          regional !== undefined &&
          regional !== null &&
          regional !== "null" &&
          regional !== "undefined"
        ) {
          //check if input is checked
          if ($(this).is(":checked")) {
            //update server
            $.ajax({
              type: "POST",
              url: terminal_africa.ajax_url,
              data: {
                action: "update_user_carrier_terminal",
                nonce: terminal_africa.nonce,
                carrierObj,
                status: "enabled"
              },
              dataType: "json",
              beforeSend: () => {
                //block element
                $(parent).block({
                  message: '<i class="fa fa-spinner fa-spin"></i>',
                  overlayCSS: {
                    background: "#fff",
                    opacity: 0.8,
                    cursor: "wait"
                  },
                  css: {
                    border: 0,
                    padding: 0,
                    backgroundColor: "transparent"
                  }
                });
              },
              success: function (response) {
                //unblock element
                $(parent).unblock();
                //izitoast success if response code is 200
                if (response.code == 200) {
                  iziToast.success({
                    title: "Success",
                    message: response.message,
                    position: "topRight",
                    timeout: 3000,
                    transitionIn: "flipInX",
                    transitionOut: "flipOutX"
                  });
                }
              }
            });
          } else {
            //update server
            $.ajax({
              type: "POST",
              url: terminal_africa.ajax_url,
              data: {
                action: "update_user_carrier_terminal",
                nonce: terminal_africa.nonce,
                carrierObj,
                status: "disabled"
              },
              dataType: "json",
              beforeSend: () => {
                //block element
                $(parent).block({
                  message: "<i class='fa fa-spinner fa-spin'></i>",
                  overlayCSS: {
                    background: "#fff",
                    opacity: 0.8,
                    cursor: "wait"
                  },
                  css: {
                    border: 0,
                    padding: 0,
                    backgroundColor: "transparent"
                  }
                });
              },
              success: function (response) {
                //unblock element
                $(parent).unblock();
                //izitoast success if response code is 200
                if (response.code == 200) {
                  iziToast.info({
                    title: "Success",
                    message: response.message,
                    position: "topRight",
                    timeout: 3000,
                    transitionIn: "flipInX",
                    transitionOut: "flipOutX"
                  });
                }
              }
            });
          }
          return;
        }
        //log data are empty
        // console.log("data are empty", carrierObj);
      });
  });

  /**
   * Hide_Shipment_Timeline
   */
  $("input[name=Hide_Shipment_Timeline]").on("change", function (e) {
    e.preventDefault();
    //switch
    let parent = $(this).parent();
    //checked
    let shipment_timeline = $(this).is(":checked") ? "true" : "false";
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_user_carrier_shipment_timeline_terminal",
        nonce: terminal_africa.nonce,
        status: shipment_timeline
      },
      dataType: "json",
      beforeSend: () => {
        //block element
        $(parent).block({
          message: "<i class='fa fa-spinner fa-spin'></i>",
          overlayCSS: {
            background: "#fff",
            opacity: 0.8,
            cursor: "wait"
          },
          css: {
            border: 0,
            padding: 0,
            backgroundColor: "transparent"
          }
        });
      },
      success: function (response) {
        //unblock element
        $(parent).unblock();
        //izitoast success if response code is 200
        if (response.code == 200) {
          iziToast.info({
            title: "Success",
            message: response.message,
            position: "topRight",
            timeout: 3000,
            transitionIn: "flipInX",
            transitionOut: "flipOutX"
          });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  });

  let checkIfShipmentRateIsTrue = () => {
    let input = $("input[name=Hide_Shipment_Rate]");
    //check if input is checked
    if (!input.is(":checked")) {
      //slide in .t-settings-page-card-notice-parent
      $(".t-settings-page-card-notice-parent").slideDown();
    } else {
      //fade out
      $(".t-settings-page-card-notice-parent").fadeOut();
    }
  };

  checkIfShipmentRateIsTrue();

  /**
   * Hide_Shipment_Rate
   */
  $("input[name=Hide_Shipment_Rate]").on("change", function (e) {
    e.preventDefault();
    //switch
    let parent = $(this).parent();
    //checked
    let shipment_rate = $(this).is(":checked") ? "true" : "false";
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_user_carrier_shipment_rate_terminal",
        nonce: terminal_africa.nonce,
        status: shipment_rate
      },
      dataType: "json",
      beforeSend: () => {
        //block element
        $(parent).block({
          message: "<i class='fa fa-spinner fa-spin'></i>",
          overlayCSS: {
            background: "#fff",
            opacity: 0.8,
            cursor: "wait"
          },
          css: {
            border: 0,
            padding: 0,
            backgroundColor: "transparent"
          }
        });
      },
      success: function (response) {
        //unblock element
        $(parent).unblock();
        //izitoast success if response code is 200
        if (response.code == 200) {
          iziToast.info({
            title: "Success",
            message: response.message,
            position: "topRight",
            timeout: 3000,
            transitionIn: "flipInX",
            transitionOut: "flipOutX"
          });

          //check if shipment_rate is true
          if (response.shipment_rate == "false") {
            //slide in .t-settings-page-card-notice-parent
            $(".t-settings-page-card-notice-parent").slideDown();
          } else {
            //fade out
            $(".t-settings-page-card-notice-parent").fadeOut();
          }
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  });

  /**
   * Enable_Terminal_Insurance
   */
  $("input[name=Enable_Terminal_Insurance]").on("change", function (e) {
    e.preventDefault();
    //switch
    let parent = $(this).parent();
    //checked
    let shipment_rate = $(this).is(":checked") ? "true" : "false";
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_user_carrier_shipment_insurance_terminal",
        nonce: terminal_africa.nonce,
        status: shipment_rate
      },
      dataType: "json",
      beforeSend: () => {
        //block element
        $(parent).block({
          message: "<i class='fa fa-spinner fa-spin'></i>",
          overlayCSS: {
            background: "#fff",
            opacity: 0.8,
            cursor: "wait"
          },
          css: {
            border: 0,
            padding: 0,
            backgroundColor: "transparent"
          }
        });
      },
      success: function (response) {
        //unblock element
        $(parent).unblock();
        //izitoast success if response code is 200
        if (response.code == 200) {
          iziToast.info({
            title: "Success",
            message: response.message,
            position: "topRight",
            timeout: 3000,
            transitionIn: "flipInX",
            transitionOut: "flipOutX"
          });
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  });

  /**
   * enable_terminal_payment_gateway
   *
   */
  $("input[name=enable_terminal_payment_gateway]").on("change", function (e) {
    e.preventDefault();
    //check if checked
    let checked = $(this).is(":checked");
    //get parent
    let parent = $(this).closest(".t-flex");
    //send to server
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_user_terminal_payment_gateway",
        nonce: terminal_africa.nonce,
        status: checked
      },
      dataType: "json",
      beforeSend: () => {
        //block element
        $(parent).block({
          message: "<i class='fa fa-spinner fa-spin'></i>",
          overlayCSS: {
            background: "#fff",
            opacity: 0.8,
            cursor: "wait"
          },
          css: {
            border: 0,
            padding: 0,
            backgroundColor: "transparent"
          }
        });

        //izitoast
        iziToast.show({
          theme: "dark",
          title: "Saving payment gateway",
          position: "topRight",
          progressBarColor: "rgb(246 146 32)",
          transitionIn: "fadeInDown",
          timeout: false
        });
      },
      success: function (response) {
        //close block
        $(parent).unblock();
        //close izitoast
        iziToast.destroy();
        if (response.code == 200) {
          //izitoast
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        } else {
          //izitoast
          iziToast.error({
            theme: "dark",
            title: "Error",
            message: response.message,
            position: "topCenter",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        }
      },
      error: function (xhr, status, error) {
        //close block
        $(parent).unblock();
        //close izitoast
        iziToast.destroy();
        //izitoast
        iziToast.error({
          theme: "dark",
          title: "Error",
          message: "Something went wrong!: " + xhr.responseText,
          position: "topCenter",
          progressBarColor: "rgb(246 146 32)",
          transitionIn: "fadeInDown"
        });
      }
    });
  });

  /**
   * terminal-africa-payment-request
   *
   */
  $(".terminal-africa-payment-request").click(function (e) {
    e.preventDefault();
    //get the button
    let button = $(this);
    //get the parent
    let parent = button.parent();
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "request_terminal_africa_payment_access",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: () => {
        //block element
        $(parent).block({
          message: "<i class='fa fa-spinner fa-spin'></i>",
          overlayCSS: {
            background: "#fff",
            opacity: 0.8,
            cursor: "wait"
          },
          css: {
            border: 0,
            padding: 0,
            backgroundColor: "transparent"
          }
        });
      },
      success: function (response) {
        //unblock element
        $(parent).unblock();
        //izitoast success if response code is 200
        if (response.code == 200) {
          iziToast.info({
            title: "Success",
            message: response.message,
            position: "bottomRight"
          });
        } else {
          //izitoast
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "bottomRight"
          });
        }
      },
      error: function (error) {
        //unblock element
        $(parent).unblock();
        //izitoast
        iziToast.error({
          title: "Error",
          message: "Something went wrong!: " + error.responseText,
          position: "bottomRight"
        });
      }
    });
  });

  //terminal_default_currency_code
  $("select[name=terminal_default_currency_code]").change(function (e) {
    e.preventDefault();
    //get the selected value
    let value = $(this).val();
    //get isoCode
    let isocode = $(this).find("option:selected").data("isocode");
    //ajax
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "save_terminal_default_currency_code",
        nonce: terminal_africa.nonce,
        currency_code: value,
        isocode
      },
      beforeSend: () => {
        //izitoast
        iziToast.show({
          theme: "dark",
          title: "Saving default currency code",
          position: "topRight",
          progressBarColor: "rgb(246 146 32)",
          transitionIn: "fadeInDown",
          timeout: false
        });
      },
      dataType: "json",
      success: function (response) {
        //close izitoast
        iziToast.destroy();
        if (response.code == 200) {
          //izitoast
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        } else {
          //izitoast
          iziToast.error({
            theme: "dark",
            title: "Error",
            message: response.message,
            position: "topCenter",
            progressBarColor: "rgb(246 146 32)",
            transitionIn: "fadeInDown"
          });
        }
      },
      error: function (xhr, status, error) {
        iziToast.error({
          theme: "dark",
          title: "Error",
          message: "Something went wrong!: " + xhr.responseText,
          position: "topCenter",
          progressBarColor: "rgb(246 146 32)",
          transitionIn: "fadeInDown"
        });
      }
    });
  });

  /**
   * Redirect to WooCommerce Flat Rate Page Settings
   */
  $(".t-notice-section-action").click(function (e) {
    e.preventDefault();
    //block ui
    $.blockUI({
      message: `<i class="fa fa-spinner fa-spin"></i> Redirecting...`,
      css: {
        border: 0,
        padding: 0,
        backgroundColor: "transparent"
      },
      overlayCSS: {
        background: "#fff",
        opacity: 0.8,
        cursor: "wait"
      }
    });
    //redirect to page
    window.location.href = $(this).data("link");
  });

  /**
   * On t-filter-transaction click
   */
  $(".t-filter-transaction").click(function (e) {
    e.preventDefault();

    //get t_start_date
    let t_start_date = $("#t_start_date").val();
    //check if not empty
    if (t_start_date === "") {
      //Swal error
      Swal.fire({
        icon: "error",
        title: "Oops...",
        confirmButtonColor: "rgb(246 146 32)",
        //confirm button text
        confirmButtonText: "Continue",
        text: "Please select a start date",
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      return;
    }
    //get t_end_date
    let t_end_date = $("#t_end_date").val();
    //check if not empty
    if (t_end_date === "") {
      //Swal error
      Swal.fire({
        icon: "error",
        title: "Oops...",
        confirmButtonColor: "rgb(246 146 32)",
        //confirm button text
        confirmButtonText: "Continue",
        text: "Please select an end date",
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      return;
    }
    //get current url
    let current_url = new URL(window.location.href);
    //get the search params from the current url
    let search_params = current_url.searchParams;

    //check if current_url has t_start_date and t_end_date and update the url
    search_params.set("startDate", t_start_date);
    search_params.set("endDate", t_end_date);

    //set the search params back to the url
    current_url.search = search_params.toString();

    //block .t-transaction-container
    $(".t-transaction-container").block({
      message: `<i class="fa fa-spinner fa-spin"></i> Loading...`,
      css: {
        border: 0,
        padding: 0,
        backgroundColor: "transparent"
      },
      overlayCSS: {
        background: "#fff",
        opacity: 0.8,
        cursor: "wait"
      }
    });

    //navigate to the new url
    window.location.href = current_url.toString();
  });

  /**
   * On click t-transaction-header-left-arrow
   */
  $(".t-transaction-header-left-arrow").click(function (e) {
    e.preventDefault();
    //slide in t-transaction-header-left-option
    $(".t-transaction-header-left-option").slideToggle();
  });
});

/**
 * Remove screen distraction
 * @return {void}
 */
let removeScreenDistraction = () => {
  //check if wpbody-content has any child other than 'style, .t-container, .clear' and remove them
  let wpBodyContent = document.querySelector("#wpbody-content");
  let wpBodyContentChildren = wpBodyContent.children;
  let wpBodyContentChildrenArray = Array.from(wpBodyContentChildren);
  //remove all children
  wpBodyContentChildrenArray.forEach((child) => {
    if (
      child.classList.contains("t-container") ||
      child.classList.contains("clear") ||
      child.tagName === "STYLE"
    ) {
      //do nothing
    } else {
      child.remove();
    }
  });
};

/**
 * Filter Terminal Transaction
 */
let filterTransaction = (elem) => {
  jQuery(document).ready(function ($) {
    var initElem = $(elem);
    //get current url
    let current_url = new URL(window.location.href);
    //get the menu
    var menu = initElem.data("menu");
    //switch
    switch (menu) {
      case "out":
        //append flow = out
        current_url.searchParams.set("flow", "out");
        break;

      case "in":
        //append flow = in
        current_url.searchParams.set("flow", "in");
        break;

      case "all":
        //check if current_url has flow and remove it
        current_url.searchParams.delete("flow");
        break;
    }
    //block .t-transaction-container
    $(".t-transaction-container").block({
      message: `<i class="fa fa-spinner fa-spin"></i> Loading...`,
      css: {
        border: 0,
        padding: 0,
        backgroundColor: "transparent"
      },
      overlayCSS: {
        background: "#fff",
        opacity: 0.8,
        cursor: "wait"
      }
    });

    //navigate to the new url
    window.location.href = current_url.toString();
  });
};

removeScreenDistraction();

/**
 * Get all get-started-buttons and enable switch
 */
jQuery(document).ready(function ($) {
  $(".get-started-button").each(function (index, element) {
    $(this).click(function (e) {
      e.preventDefault();
      //remove all active .t-get-started-active
      $(".get-started-button").removeClass("t-get-started-active");
      //add active to this element
      $(element).addClass("t-get-started-active");
      //get data view
      let dataView = $(element).data("view");
      //hide all views
      $(".get-started-custom-view").hide();
      //switch
      switch (dataView) {
        case "get-started-payment-section":
          //show initial
          $(".get-started-payment-section").show();
          //get the first index .get-started-button
          let firstI = $(".get-started-button:nth-child(2)");
          //update inner img with terminal_africa.shipping_active_img
          firstI.find("img").attr("src", terminal_africa.shipping_inactive_img);
          //second index .get-started-button
          let thirdI = $(".get-started-button:nth-child(3)");
          //update inner img with terminal_africa.support_inactive_img
          thirdI.find("img").attr("src", terminal_africa.support_inactive_img);
          break;

        case "get-started-shipping-section":
          //show initial
          $(".get-started-shipping-section").show();
          //get the first index .get-started-button
          let firstIndex = $(".get-started-button:nth-child(2)");
          //update inner img with terminal_africa.shipping_active_img
          firstIndex
            .find("img")
            .attr("src", terminal_africa.shipping_active_img);
          //second index .get-started-button
          let thirdIndex = $(".get-started-button:nth-child(3)");
          //update inner img with terminal_africa.support_inactive_img
          thirdIndex
            .find("img")
            .attr("src", terminal_africa.support_inactive_img);
          break;

        case "get-started-support-section":
          //show secondView
          $(".get-started-support-section").show();
          //get the first index .get-started-button
          let firstIndex2 = $(".get-started-button:nth-child(2)");
          //update inner img with terminal_africa.shipping_inactive_img
          firstIndex2
            .find("img")
            .attr("src", terminal_africa.shipping_inactive_img);
          //second index .get-started-button
          let thirdIndex2 = $(".get-started-button:nth-child(3)");
          //update inner img with terminal_africa.support_active_img
          thirdIndex2
            .find("img")
            .attr("src", terminal_africa.support_active_img);
          break;
      }
    });
  });

  /**
   * Terminal Single Authentication
   *
   * @param {string} elem
   */
  $(".t-single-auth").click(function (e) {
    e.preventDefault();
    //url to visit
    let url = $(this).data("url");
    //check if url is empty
    if (url === "" || url === undefined || url === null) {
      //throw error
      Swal.fire({
        icon: "error",
        title: "Oops...",
        confirmButtonColor: "rgb(246 146 32)",
        //confirm button text
        confirmButtonText: "Close",
        text: "Authentication url is empty",
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      //return
      return;
    }
    //get domain url and site name
    let domain = $(this).data("domain");
    //encode base64 domain
    domain = btoa(domain);
    let site_name = $(this).data("site-name");
    //append domain and site name to url
    url = url + "?domain=" + domain + "&site_name=" + site_name;
    //redirect to url
    window.open(url, "_self");
  });

  /**
   * Onload event handler for Oauth
   *  1. Check if current url has terminal_token
   *  2. Check if terminal token is empty
   *  3. Check if terminal token is valid
   *  @param {void}
   */
  let terminalOauth = () => {
    //check if current url has terminal_token
    let current_url = new URL(window.location.href);
    //terminal token
    let terminal_token = current_url.searchParams.get("terminal_token");
    //check if terminal token is empty
    if (
      terminal_token === "" ||
      terminal_token === null ||
      terminal_token === undefined
    ) {
      return;
    }
    //decode the terminal_token
    let decodedString = atob(terminal_token);
    //split the decoded string by :
    let splitString = decodedString.split(":");
    //check if splitString is empty
    if (splitString.length < 2) {
      //swal
      Swal.fire({
        icon: "error",
        title: "Oops...",
        confirmButtonColor: "rgb(246 146 32)",
        //confirm button text
        confirmButtonText: "Close",
        text: "Invalid authentication token",
        footer: `
          <div>
            <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
          </div>
        `
      });
      return;
    }
    //get pk
    let pk = splitString[0];
    //get sk
    let sk = splitString[1];
    //authenticate
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "terminal_africa_auth",
        public_key: pk,
        secret_key: sk,
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      beforeSend: function () {
        // Swal loader
        Swal.fire({
          title: "Authenticating...",
          text: "Please wait...",
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
        //close loader
        Swal.close();
        //check response is 200
        if (response.code == 200) {
          //swal success
          Swal.fire({
            icon: "success",
            type: "success",
            title: "Success!",
            text: "Terminal Africa has been successfully authenticated",
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            iconColor: "rgb(246 146 32)",
            footer: `
                  <div>
                    <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                  </div>
                `
          }).then((result) => {
            if (result.value) {
              //check if terminal_africa.getting_started_url is none
              if (terminal_africa.getting_started_url == "none") {
                //do nothing
              } else {
                //show loading
                Swal.fire({
                  title: "Redirecting...",
                  text: "Please wait...",
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
                //redirect to getting started page
                window.location.href = terminal_africa.getting_started_url;
              }
            }
          });
        } else {
          //swal error
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
        }
      },
      error: function (xhr, status, error) {
        //close loader
        Swal.close();
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
  };

  //load
  terminalOauth();

  /**
   * Update user settings
   * @return {void}
   */
  let updateUserSettings = () => {
    //send ajax request
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_terminal_user_settings",
        nonce: terminal_africa.nonce
      },
      dataType: "json",
      success: function (response) {
        // console.log(response);
      },
      error: function (xhr, status, error) {
        console.log(error);
      }
    });
  };

  /**
   * Check current page is matching terminal-africa-settings
   * @return {void}
   */
  if ($(".t-settings-page").length > 0) {
    //update user settings
    updateUserSettings();
  }

  /**
   * t-switch-wallet
   *
   */
  $(".t-switch-wallet").on("change", function () {
    //get selected value
    let selectedValue = $(this).val();
    //update currency
    $.ajax({
      type: "POST",
      url: terminal_africa.ajax_url,
      data: {
        action: "update_terminal_wallet_currency",
        nonce: terminal_africa.nonce,
        currency: selectedValue
      },
      dataType: "json",
      beforeSend: function () {
        // Swal loader
        Swal.fire({
          title: "Please wait...",
          text: "Switching wallet",
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
        //check if responce code is 200
        if (response.code == 200) {
          //reload page
          location.reload();
        } else {
          //Swal error
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: response.message,
            confirmButtonColor: "rgb(246 146 32)",
            cancelButtonColor: "rgb(0 0 0)",
            footer: `
              <div>
                <img src="${terminal_africa.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
              </div>
            `
          });
        }
      },
      error: function (xhr, status, error) {
        //close loader
        Swal.close();
        //show error
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Something went wrong!: " + xhr.responseText
        });
      }
    });
  });
});

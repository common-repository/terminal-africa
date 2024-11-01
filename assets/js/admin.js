jQuery(document).ready(function ($) {
  //get a with arial-label Deactivate Terminal Africa
  $("a[aria-label='Deactivate Terminal Africa']").on("click", function (e) {
    //prevent default
    e.preventDefault();
    //get parent with tr
    let parent = $(this).closest("tr");
    //get link
    let link = $(this).attr("href");
    //show swal confirm
    Swal.fire({
      title: "Are you sure?",
      text: "You want to deactivate Terminal Africa? This will deactivate the plugin and remove plugin data",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(246 146 32)",
      cancelButtonColor: "rgb(0 0 0)",
      footer: `
        <div>
          <img src="${terminal_africa_admin.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
        </div>
      `,
      confirmButtonText: "Yes, deactivate it!"
    }).then((result) => {
      //if result is true
      if (result.value) {
        //do ajax
        $.ajax({
          type: "POST",
          url: terminal_africa_admin.ajax_url,
          data: {
            action: "deactivate_terminal_africa",
            nonce: terminal_africa_admin.nonce
          },
          dataType: "json",
          beforeSend: () => {
            //block element
            parent.block({
              message: "",
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
            //if response code is 200
            if (response.code == 200) {
              //reload page
              window.location.href = link;
            } else {
              //unblock element
              parent.unblock();
              //show error
              Swal.fire({
                title: "Error",
                text: response.message,
                icon: "error",
                footer: `
                    <div>
                        <img src="${terminal_africa_admin.plugin_url}/img/logo-footer.png" style="height: 30px;" alt="Terminal Africa">
                    </div>
                    `
              });
            }
          }
        });
      }
    });
  });

  /**
   * Close notice
   * .terminal-notice-dismiss
   * @return {void}
   */
  $(".terminal-notice-dismiss").click(function (e) {
    e.preventDefault();
    //send ajax request
    $.ajax({
      type: "POST",
      url: terminal_africa_admin.ajax_url,
      data: {
        action: "terminal_africa_close_notice",
        nonce: terminal_africa_admin.nonce
      }
    });
    //remove the parent
    $(".terminal-custom-notice-wp").fadeOut(500, function () {
      $(this).remove();
    });
  });
});

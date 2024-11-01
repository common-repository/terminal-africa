/**
 * Terminal Africa Admin Loggin
 * @since 1.10.3
 * @author Adeleye Ayodeji
 */
class TerminalAfricaAdminLoggin {
  /**
   * Constructor
   */
  constructor() {
    this.init();
  }

  /**
   * Init
   * @return {void}
   * @since 1.10.3
   */
  init() {
    //check if plugin is logged
    //use fetch for ajax check_if_terminal_plugin_already_logged
    let ajaxUrl = terminal_africa.ajax_url;
    let nonce = terminal_africa.nonce;
    let data = {
      action: "check_if_terminal_plugin_already_logged",
      nonce: nonce
    };
    //build the url
    ajaxUrl = `${ajaxUrl}?action=${data.action}&nonce=${data.nonce}`;
    fetch(ajaxUrl, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-WP-Nonce": nonce
      },
      credentials: "same-origin",
      mode: "cors",
      cache: "default"
    })
      .then((res) => res.json())
      .then((res) => {
        // console.log(res);
      })
      .catch((err) => {
        console.log(err);
      });
  }
}

//init
new TerminalAfricaAdminLoggin();

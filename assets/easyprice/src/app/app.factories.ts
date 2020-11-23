/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

export function RestangularDefaultConfigFactory (RestangularProvider) {
  RestangularProvider.setBaseUrl(window['easyprice'].api_base_url);
  RestangularProvider.setDefaultHeaders({'Authorization': 'Bearer ' + window['easyprice'].access_token});
}

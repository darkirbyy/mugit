import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on the switch theme modal
 */
export default class extends Controller {
  static targets = ['retry', 'alert', 'spinner'];

  initialize() {}

  connect() {
    if (this.retryTarget) {
      this.retryTarget.addEventListener('click', () => {
        this.alertTarget.classList.add('hidden');
        // FlowbiteInstances.getInstance('Dismiss', this.alertTarget.id).hide()
        this.spinnerTarget.classList.remove('hidden');
      });
    }
  }
}

import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be place on a flash to dismiss it
 */
export default class extends Controller {
  initialize() {}

  dismiss(event) {
    event.preventDefault();
    this.element.remove();
  }
}

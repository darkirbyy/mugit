import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be place on button to close a flash message
 */
export default class extends Controller {
  static targets = ['button'];

  initialize() {}

  connect() {
    this.buttonTarget.addEventListener('click', () => {
      this.element.remove();
    });
  }
}

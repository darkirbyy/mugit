import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on a turbo-frame to reload it
 * while keeping its original spinner
 */
export default class extends Controller {
  initialize() {
    this.initialSpinnerHTML = this.element.innerHTML;
  }

  reload(event) {
    event.preventDefault();

    // remove all child
    while (this.element.firstChild) {
      this.element.removeChild(this.element.lastChild);
    }

    // add initial spinner as a child
    const initialSpinner = document.createElement('div');
    initialSpinner.innerHTML = this.initialSpinnerHTML;
    this.element.appendChild(initialSpinner);

    // trigger reload
    this.element.reload();
  }
}

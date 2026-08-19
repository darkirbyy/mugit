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
    this.restoreSpinner();
    this.element.reload();
  }

  submit(event) {
    this.restoreSpinner();
  }

  restoreSpinner() {
    // hide all child
    Array.from(this.element.children).forEach((child) => {
      child.classList.add('hidden');
    });

    // add initial spinner as a child
    const initialSpinner = document.createElement('div');
    initialSpinner.innerHTML = this.initialSpinnerHTML;
    this.element.appendChild(initialSpinner);
  }
}

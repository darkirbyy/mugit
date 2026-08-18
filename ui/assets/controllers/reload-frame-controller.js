import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on a button to reload the closest turbo-frame
 */
export default class extends Controller {
  static values = {
    spinnerHtml: String,
  };
  initialize() {}

  connect() {
    this.element.addEventListener('click', (event) => {
      event.preventDefault();
      const frame = this.element.closest('turbo-frame');

      // remove all child
      while (frame.firstChild) {
        frame.removeChild(frame.lastChild);
      }

      // add spinner as a child
      let div = document.createElement('div');
      div.innerHTML = this.spinnerHtmlValue;
      frame.appendChild(div);

      // trigger reload
      frame.reload();
    });
  }
}

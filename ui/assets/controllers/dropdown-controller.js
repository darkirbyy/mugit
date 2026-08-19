import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on a item of a dropdown if it has extra content
 */
export default class extends Controller {
  static targets = ['button', 'content'];
  static values = {
    classOpen: String,
    classUnopen: String,
  };

  initialize() {
    this.open = false;
    this.classOpen = this.classOpenValue.split(' ');
    this.classUnopen = this.classUnopenValue.split(' ');
    // todo : use something more robust than lastChildElement and query selector
    this.icon = this.buttonTarget.lastElementChild.querySelector('span');
  }

  connect() {
    this.buttonTarget.addEventListener('click', () => {
      this.open = !this.open;
      this.contentTarget.classList.toggle('hidden', !this.open);
      this.classOpen.forEach((className) => this.icon.classList.toggle(className, this.open));
      this.classUnopen.forEach((className) => this.icon.classList.toggle(className, !this.open));
    });
  }
}

import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on a item of a dropdown if it has extra content
 */
export default class extends Controller {
  static targets = ['button', 'content'];

  initialize() {
    this.open = false;
    // todo : use something more robust than lastChildElement and query selector
    this.icon = this.buttonTarget.lastElementChild.querySelector('span');
  }

  connect() {
    this.buttonTarget.addEventListener('click', () => {
      this.open = !this.open;
      this.contentTarget.classList.toggle('hidden', !this.open);
      this.buttonTarget.classList.toggle('dark:bg-zinc-800', this.open);
      this.buttonTarget.classList.toggle('bg-zinc-300', this.open);
      this.buttonTarget.classList.toggle('rounded-t', this.open);
      this.buttonTarget.classList.toggle('rounded-br', this.open);
      this.buttonTarget.classList.toggle('rounded', !this.open);
      this.icon.classList.toggle('ri-arrow-down-s-line', this.open);
      this.icon.classList.toggle('ri-arrow-right-s-line', !this.open);
    });
  }
}

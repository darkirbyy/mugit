import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be placed on an accordion
 */
export default class extends Controller {
  static targets = ['button', 'content'];
  static values = {
    autoOpen: Boolean,
    menuItem: Boolean,
  };

  initialize() {
    this.open = this.autoOpenValue ?? false;
    this.menuItem = this.menuItemValue ?? false;
    this.icon = this.buttonTarget.getElementsByClassName('ri-arrow-right-s-line')[0];
  }

  connect() {
    this.toggleContent();

    this.buttonTarget.addEventListener('click', () => {
      this.open = !this.open;
      this.toggleContent();
    });
  }

  toggleContent() {
    this.contentTargets.forEach((content) => {
      content.classList.toggle('hidden', !this.open);
    });
    if (this.menuItem) {
      this.buttonTarget.classList.toggle('dark:bg-zinc-800', this.open);
      this.buttonTarget.classList.toggle('bg-zinc-300', this.open);
      this.buttonTarget.classList.toggle('rounded-t', this.open);
      this.buttonTarget.classList.toggle('rounded-br', this.open);
      this.buttonTarget.classList.toggle('rounded', !this.open);
    }
    this.icon.classList.toggle('ri-arrow-down-s-line', this.open);
    this.icon.classList.toggle('ri-arrow-right-s-line', !this.open);
  }
}

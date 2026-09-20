import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be place on a table with "copy to clipboard" buttons
 */
export default class extends Controller {
  static targets = ['cell'];
  static values = {
    copiedText: String,
  };

  initialize() {
    this.copiedText = this.copiedTextValue;
    this.uncopiedText = '';
    if (this.cellTargets.length > 0) {
      this.uncopiedText = this.cellTargets[0].querySelector('[data-custom=button]').getAttribute('data-tooltip');
    }
  }

  connect() {
    this.cellTargets.forEach((cell) => {
      const cloneURL = cell.querySelector('[data-custom=clone-url]');
      const button = cell.querySelector('[data-custom=button]');
      button.addEventListener('click', () => {
        navigator.clipboard.writeText(cloneURL.textContent);
        this.cellTargets.forEach((otherCell) => {
          const otherButton = otherCell.querySelector('[data-custom=button]');
          this.updateButton(otherButton, button == otherButton);
        });
      });
    });
  }

  updateButton(button, checked) {
    const icon = button.querySelector('[data-custom=icon]');
    icon.classList.toggle('ri-check-double-line', checked);
    icon.classList.toggle('text-green-700', checked);
    icon.classList.toggle('dark:text-green-300', checked);
    icon.classList.toggle('ri-file-copy-line', !checked);
    button.setAttribute('data-tooltip', checked ? this.copiedText : this.uncopiedText);
  }
}

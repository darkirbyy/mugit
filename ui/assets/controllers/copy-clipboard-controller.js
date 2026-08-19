import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be place on a table with "copy to clipboard" buttons
 */
export default class extends Controller {
  static targets = ['cell'];
  static values = {
    classChecked: String,
    classUnchecked: String,
    copiedText: String,
  };

  initialize() {
    this.classChecked = this.classCheckedValue.split(' ');
    this.classUnchecked = this.classUncheckedValue.split(' ');
    this.copiedText = this.copiedTextValue;
    this.uncopiedText =
      this.cellTargets.length > 0 ? this.cellTargets[0].querySelector('button').getAttribute('data-tooltip') : '';
  }

  connect() {
    this.cellTargets.forEach((cell) => {
      // todo : use something more robust than these query selectors
      const cloneURL = cell.querySelector('span');
      const button = cell.querySelector('button');
      button.addEventListener('click', () => {
        navigator.clipboard.writeText(cloneURL.textContent);
        this.cellTargets.forEach((cell2) => {
          const button2 = cell2.querySelector('button');
          const icon2 = cell2.querySelector('button span');
          this.updateIcon(button2, icon2, button == button2);
        });
      });
    });
  }

  updateIcon(button, icon, checked) {
    this.classChecked.forEach((className) => icon.classList.toggle(className, checked));
    this.classUnchecked.forEach((className) => icon.classList.toggle(className, !checked));
    button.setAttribute('data-tooltip', checked ? this.copiedText : this.uncopiedText);
  }
}

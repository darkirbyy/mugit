import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller that MUST be place on a table with "copy to clipboard" buttons
 */
export default class extends Controller {
  static targets = ['cell'];
  static values = {
    classChecked: String,
    classUnchecked: String,
  };

  initialize() {
    this.classChecked = this.classCheckedValue.split(' ');
    this.classUnchecked = this.classUncheckedValue.split(' ');
  }

  connect() {
    this.cellTargets.forEach((cell) => {
      // todo : use something more robust than these query selectors
      const cloneURL = cell.querySelector('span');
      const button = cell.querySelector('button');
      const icon = cell.querySelector('button span');
      button.addEventListener('click', () => {
        navigator.clipboard.writeText(cloneURL.textContent);
        this.cellTargets.forEach((cell2) => {
          const icon2 = cell2.querySelector('button span');
          this.updateIcon(icon2, icon == icon2);
        });
      });
    });
  }

  updateIcon(icon, checked) {
    this.classChecked.forEach((className) => icon.classList.toggle(className, checked));
    this.classUnchecked.forEach((className) => icon.classList.toggle(className, !checked));
  }
}

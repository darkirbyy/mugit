import { Controller } from '@hotwired/stimulus';

/* global FlowbiteInstances */

/**
 * Stimulus controller that MUST be placed on the dropdown itself to use a close button
 */
export default class extends Controller {
  close() {
    FlowbiteInstances.getInstance('Dropdown', this.element.id).hide();
  }
}

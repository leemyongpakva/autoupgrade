import DomLifecycle from '../types/DomLifecycle';
import Hydration from '../utils/Hydration';

export default class ModalContainer implements DomLifecycle {
  public static readonly cancelEvent = 'cancel';
  public static readonly okEvent = 'ok';

  public static readonly containerId = 'ua_modal';

  public mount(): void {
    this.modalContainer.addEventListener(Hydration.hydrationEventName, this.#displayModal);
    this.modalContainer.addEventListener('click', this.#onClick);
    this.modalContainer.addEventListener(ModalContainer.cancelEvent, this.#closeModal);
    this.modalContainer.addEventListener(ModalContainer.okEvent, this.#closeModal);
  }

  public beforeDestroy(): void {
    this.modalContainer.removeEventListener(Hydration.hydrationEventName, this.#displayModal);
    this.modalContainer.removeEventListener('click', this.#onClick);
    this.modalContainer.removeEventListener(ModalContainer.cancelEvent, this.#closeModal);
    this.modalContainer.removeEventListener(ModalContainer.okEvent, this.#closeModal);
  }

  public get modalContainer(): HTMLElement {
    const container = document.getElementById(ModalContainer.containerId);

    if (!container) {
      throw new Error('Cannot find modal container to initialize.');
    }
    return container;
  }

  #displayModal(): void {
    const modal = document.getElementById(ModalContainer.containerId)?.getElementsByClassName('dialog')[0] as HTMLDialogElement;
    if (modal) {
      modal.showModal();
    }
  }

  #onClick(ev: Event): void {
    const target = ev.target ? (ev.target as HTMLElement) : null;
    const modal = target?.closest('.dialog');

    if (modal) {
      if (target?.closest("[data-dismiss='modal']")) {
        modal.dispatchEvent(new Event(ModalContainer.cancelEvent, { bubbles: true }));
      }  else if (!modal.contains(target) || target === modal) {
        modal.dispatchEvent(new Event(ModalContainer.cancelEvent, { bubbles: true }));
      } else if (target?.closest(".modal-footer button:not([data-dismiss='modal'])")) {
        modal.dispatchEvent(new Event(ModalContainer.okEvent, { bubbles: true }));
      }
    }
  }

  #closeModal(ev: Event): void {
    const modal = ev.target as HTMLDialogElement;
    if (modal) {
      modal.close();
    }
  }
}

import Hydration from '../utils/Hydration';

export default class ModalContainer {
  public static readonly cancelEvent = 'cancel';
  public static readonly okEvent = 'ok';

  public static readonly containerId = 'ua_modal';

  public init(): void {
    // Avoid reattaching the same event listener several times.
    this.unset();
    this.modalContainer.addEventListener(Hydration.hydrationEventName, this.displayModal);
    this.modalContainer.addEventListener('click', this.onClick);
    this.modalContainer.addEventListener(ModalContainer.cancelEvent, this.closeModal);
    this.modalContainer.addEventListener(ModalContainer.okEvent, this.closeModal);
  }

  public unset(): void {
    this.modalContainer.removeEventListener(Hydration.hydrationEventName, this.displayModal);
    this.modalContainer.removeEventListener('click', this.onClick);
    this.modalContainer.removeEventListener(ModalContainer.cancelEvent, this.closeModal);
    this.modalContainer.removeEventListener(ModalContainer.okEvent, this.closeModal);
  }

  private displayModal(): void {
    $(
      document.getElementById(ModalContainer.containerId)?.getElementsByClassName('modal') || []
    ).modal('show');
  }

  private onClick(ev: Event): void {
    const target = ev.target ? (ev.target as HTMLElement) : null;
    const modal = target?.closest('.modal');

    if (modal && target?.closest("[data-dismiss='modal']")) {
      modal.dispatchEvent(new Event(ModalContainer.cancelEvent, { bubbles: true }));
    } else if (modal && target?.closest(".modal-footer button:not([data-dismiss='modal'])")) {
      modal.dispatchEvent(new Event(ModalContainer.okEvent, { bubbles: true }));
    }
  }

  private closeModal(ev: Event): void {
    const modal = ev.target;
    if (modal) {
      $(modal).modal('hide');
    }
  }

  private get modalContainer(): HTMLElement {
    const container = document.getElementById(ModalContainer.containerId);

    if (!container) {
      throw new Error('Cannot find modal container to initialize.');
    }
    return container;
  }
}

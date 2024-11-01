import Hydration from "../utils/Hydration";

export default class ModalContainer {
    public static cancelEvent = 'cancel';
    public static okEvent = 'ok';

    public static containerId = 'ua_modal';
    public init(): void
    {
        const modalContainer = document.getElementById(ModalContainer.containerId);

        if (modalContainer) {
            modalContainer.addEventListener(
                Hydration.hydrationEventName, () => this.displayModal(),
            );
            // Event delegation
            modalContainer.addEventListener('click', (ev: Event) => {
                const modal = ev.target?.closest(".modal");
                if (modal && ev.target?.closest("[data-dismiss='modal']")) {
                    modal.dispatchEvent(new Event(ModalContainer.cancelEvent, {bubbles: true}));
                } else if(modal && ev.target?.closest(".modal-footer button:not([data-dismiss='modal'])")) {
                    modal.dispatchEvent(new Event(ModalContainer.okEvent, {bubbles: true}))
                }
            });

            modalContainer.addEventListener(
                ModalContainer.cancelEvent, this.closeModal,
            );
            modalContainer.addEventListener(
                ModalContainer.okEvent, this.closeModal,
            );
        }
    }

    private displayModal(): void {
        Array.from(
            document.getElementById(ModalContainer.containerId)?.getElementsByClassName('modal') || []
        ).forEach((modal: HTMLElement) => {
            modal.style.display = 'block';
            // Need to wait a bit to make sure the DOM is refreshed to trigger the animation
            setTimeout(() => modal.classList.add('in'), 50);
        });
    }

    private closeModal(ev: Event): void {
        const modal = ev.target;
        if (modal) {
            modal.classList.remove('in');
            // Let the animation play before hiding the modal
            setTimeout(() => modal.style.display = null, 500);
        }
    }
}
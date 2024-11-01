import Hydration from "../utils/Hydration";

export default class ModalContainer {
    public static containerId = 'ua_modal';
    public init(): void
    {
        document.getElementById(ModalContainer.containerId)?.addEventListener(
            Hydration.hydrationEventName, () => this.displayModal(),
        );
    }

    private displayModal(): void {
        Array.from(
            document.getElementById(ModalContainer.containerId)?.getElementsByClassName('modal') || []
        ).forEach((modal: HTMLElement) => {
            modal.style.display = 'block';
            modal.classList.add('in');
        });
}
}
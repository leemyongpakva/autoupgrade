import api from '../api/RequestHandler';
import { ApiResponseAction } from '../types/apiTypes';
import DomLifecycle from '../types/DomLifecycle';
import { ProcessContainerCallbacks } from '../types/Process';
import Process from '../utils/Process';
import ProgressTracker from './ProgressTracker';

export default class ProcessContainer implements DomLifecycle {
  #progressTracker: ProgressTracker = new ProgressTracker(this.#progressTrackerContainer);

  constructor(
    private readonly initialAction: string,
    private readonly callbacks: ProcessContainerCallbacks
  ) {}

  public mount = async (): Promise<void> => {
    this.#progressTracker.mount();

    const process = new Process({
      onProcessResponse: this.#onProcessResponse,
      onProcessEnd: this.#onProcessEnd,
      onError: this.#onError
    });

    this.#enableExitConfirmation();

    await process.startProcess(this.initialAction);
  };

  public beforeDestroy(): void {
    this.#progressTracker.beforeDestroy();
    this.#disableExitConfirmation();
  }

  get #progressTrackerContainer(): HTMLDivElement {
    const progressTrackerContainer = document.querySelector(
      '[data-component="progress-tracker"]'
    ) as HTMLDivElement;

    if (!progressTrackerContainer) {
      throw new Error('Progress tracker container not found');
    }

    return progressTrackerContainer;
  }

  #onProcessResponse = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
  };

  #onProcessEnd = async (response: ApiResponseAction): Promise<void> => {
    this.#disableExitConfirmation();
    if (response.error) {
      this.#onError(response);
    } else {
      await api.post(this.#progressTrackerContainer.dataset.successRoute!);
    }
  };

  #onError = (response: ApiResponseAction): void => {
    this.#disableExitConfirmation();
    this.#progressTracker.updateProgress(response);
    this.#progressTracker.endProgress();
    this.#displayErrorAlert();
    this.#displayErrorButtons();

    this.callbacks.onError();
  };

  #displayErrorAlert = () => {
    const alertContainer = document.getElementById('error-alert');

    if (!alertContainer) {
      throw new Error('Error alert container not found');
    }

    alertContainer.classList.remove('hidden');
  };

  #displayErrorButtons = () => {
    const buttonsContainer = document.getElementById('error-buttons');

    if (!buttonsContainer) {
      throw new Error('Error buttons container not found');
    }

    buttonsContainer.classList.remove('hidden');
  };

  #enableExitConfirmation = (): void => {
    window.addEventListener('beforeunload', this.#handleBeforeUnload);
  };

  #disableExitConfirmation = (): void => {
    window.removeEventListener('beforeunload', this.#handleBeforeUnload);
  };

  #handleBeforeUnload = (event: Event): void => {
    event.preventDefault();

    // Included for legacy support, e.g. Chrome/Edge < 119
    event.returnValue = true;
  };
}

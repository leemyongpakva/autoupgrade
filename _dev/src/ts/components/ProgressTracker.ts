import ComponentAbstract from "./ComponentAbstract";
import ProgressBar from "./ProgressBar";
import LogsSummary from "./LogsSummary";
import LogsViewer from "./LogsViewer";
import {ApiResponseAction} from "../types/apiTypes";
import api from '../api/RequestHandler';

export default class ProgressTracker extends ComponentAbstract {
  #logsSummary: LogsSummary;
  #progressBar: ProgressBar;
  #logsViewer: LogsViewer;

  constructor(element: HTMLElement) {
    super(element);

    this.#logsSummary = new LogsSummary(this.#logsSummaryContainer);
    this.#progressBar = new ProgressBar(this.#progressBarContainer);
    this.#logsViewer = new LogsViewer(this.#logsViewerContainer);
  }

  #logsSummaryContainer = this.queryElement<HTMLDivElement>(
    '[data-component="logs-summary"]',
    'Logs summary not found'
  );

  #progressBarContainer = this.queryElement<HTMLDivElement>(
    '[data-component="progress-bar"]',
    'Progress bar not found'
  );

  #logsViewerContainer = this.queryElement<HTMLDivElement>(
    '[data-component="logs-viewer"]',
    'Logs viewer not found'
  );

  public launchAction = async (action: string): Promise<void> => {
    const actionFormData = new FormData();
    actionFormData.set('action', action);

    const response = await api.post('', actionFormData) as ApiResponseAction;

    await this.#handleResponseAction(response);
  }

  #updateProgress(data: ApiResponseAction) {
    this.#logsSummary.setLogsSummaryText(String(data.next_desc));
    this.#progressBar.setProgressPercentage(data.nextParams.progressPercentage);
    this.#logsViewer.addLogs(data.nextQuickInfo);
  }

  #handleResponseAction = async (response: ApiResponseAction): Promise<void> => {
    if ('nextQuickInfo' in response) {
      this.#updateProgress(response as ApiResponseAction);

      // As long as we have a next step returned by the API we continue to call it
      if (response.next) {
        await this.launchAction(response.next);
      } else {
        if (response.error) {
          // At the end of the process if there is an error we display the summary
          this.#logsViewer.displaySummary();
        } else {
          // If no error we go to the next page
          const successRoute = this.element.dataset.successRoute;
          if (successRoute === undefined) {
            throw new Error('Success route not found');
          }
          await api.post(successRoute);
        }
      }
    }
  }
}

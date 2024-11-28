import ComponentAbstract from './ComponentAbstract';
import ProgressBar from './ProgressBar';
import LogsSummary from './LogsSummary';
import LogsViewer from './LogsViewer';
import { ApiResponseAction } from '../types/apiTypes';
import { Destroyable } from '../types/DomLifecycle';

export default class ProgressTracker extends ComponentAbstract implements Destroyable {
  #logsSummary: NonNullable<LogsSummary> = new LogsSummary(this.#logsSummaryContainer);
  #progressBar: NonNullable<ProgressBar> = new ProgressBar(this.#progressBarContainer);
  #logsViewer: NonNullable<LogsViewer> = new LogsViewer(this.#logsViewerContainer);

  public beforeDestroy = () => {
    this.#logsViewer.beforeDestroy();
  };

  get #logsSummaryContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="logs-summary"]',
      'Logs summary not found'
    );
  }

  get #progressBarContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="progress-bar"]',
      'Progress bar not found'
    );
  }

  get #logsViewerContainer() {
    return this.queryElement<HTMLDivElement>(
      '[data-component="logs-viewer"]',
      'Logs viewer not found'
    );
  }

  /**
   * @public
   * @param {ApiResponseAction} data - API response data containing the progress information,
   * next description, and log details.
   * @returns {void}
   * @description Updates the progress tracker components:
   * - Updates the log summary with the next description.
   * - Updates the progress bar with the progress percentage.
   * - Adds new logs to the logs viewer.
   */
  public updateProgress(data: ApiResponseAction): void {
    this.#logsSummary.setLogsSummaryText(data.next_desc ?? '');
    this.#progressBar.setProgressPercentage(data.nextParams.progressPercentage);
    this.#logsViewer.addLogs(data.nextQuickInfo);
  }

  /**
   * @public
   * @returns {void}
   * @description Ends the progress tracking and displays a summary of error logs in the logs viewer.
   */
  public endProgress(): void {
    // Todo: we need to retrieve the download link
    this.#logsViewer.displaySummary('download/logs/link.txt');
  }
}

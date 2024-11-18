import ComponentAbstract from "./ComponentAbstract";
import ProgressBar from "./ProgressBar";
import LogsSummary from "./LogsSummary";
import LogsViewer from "./LogsViewer";
import {ApiResponseAction} from "../types/apiTypes";

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

  public updateProgress(data: ApiResponseAction) {
    this.#logsSummary.setLogsSummaryText(String(data.next_desc));
    this.#progressBar.setProgressPercentage(data.nextParams.progressPercentage);
    this.#logsViewer.addLogs(data.nextQuickInfo);
  }
}

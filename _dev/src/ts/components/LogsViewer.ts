import ComponentAbstract from './ComponentAbstract';
import { LogEntry, Log, Severity } from '../types/logsTypes';
import { parseLogWithSeverity, debounce } from '../utils/logsUtils';
import DomLifecycle from '../types/DomLifecycle';
import { logStore } from '../store/LogStore';

export default class LogsViewer extends ComponentAbstract implements DomLifecycle {
  #logsIndexHeight: Map<number, number> = new Map();
  #logsListHeight: number = this.#logsList.clientHeight;

  // -- virtual scroll configuration --
  readonly #bufferSize = 2; // The multiplier for the viewport height used to define the buffer zone for virtual scrolling.
  readonly #debounceTime = 200; // The delay time (in ms) for debouncing the `refreshView` method.
  readonly #logBeforeScroll = 50; // The number of logs to process before automatically scrolling to the bottom.

  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );
  #logsSummary = this.queryElement<HTMLDivElement>(
    '[data-slot-component="summary"]',
    'Logs summary not found'
  );
  #templateSummary = this.queryElement<HTMLTemplateElement>(
    '#log-summary',
    'Template summary not found'
  );

  get #logsScroll() {
    return this.queryElement<HTMLDivElement>(
      '[data-slot-component="scroll"]',
      'Logs scroll not found'
    );
  }

  get #logsList() {
    return this.queryElement<HTMLDivElement>('[data-slot-component="list"]', 'Logs list not found');
  }

  public mount = () => {
    this.#logsScroll.addEventListener('scroll', this.#debouncedRefreshView);

    // delay needed because of side menu toggle on small screens
    // we set width to prevent the modification of the height of
    // the logs which would require too many resources to recalculate everything
    setTimeout(() => {
      this.#logsList.style.width = `${this.#logsList.offsetWidth}px`;
    }, 1000);
  };

  public beforeDestroy = () => {
    this.#logsScroll.removeEventListener('scroll', this.#debouncedRefreshView);
    this.#logsSummary.removeEventListener('click', this.#handleLinkEvent);
  };

  /**
   * @public
   * @param logs
   */
  public addLogs = (logs: string[]): void => {
    let count = 0;

    logs.forEach((log) => {
      const id = logStore.getLogsLength();
      const logEntry = parseLogWithSeverity(log);
      const HTMLElement = this.#createLogLine(logEntry);
      this.#logsList.appendChild(HTMLElement);

      const height = HTMLElement.offsetHeight;
      const offsetTop = HTMLElement.offsetTop;

      logStore.addLog({
        ...logEntry,
        height,
        offsetTop,
        HTMLElement
      });

      this.#logsIndexHeight.set(id, offsetTop);
      this.#logsListHeight += height;

      count += 1;

      if (count > this.#logBeforeScroll) {
        this.#scrollToBottom();
        count = 0;
      }
    });

    this.#scrollToBottom();
  };

  #createLogLine = (logEntry: LogEntry): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

    logLine.classList.add(`logs__line--${logEntry.severity}`);
    logLine.setAttribute('data-status', logEntry.severity);
    logLine.textContent = logEntry.message;

    return logLine;
  };

  #createSummaryLogLine = (logEntry: LogEntry): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;
    const logLineContent = logLine.querySelector('.logs__line-content') as HTMLDivElement;

    logLine.classList.add(`logs__line--${logEntry.severity}`);
    logLine.setAttribute('data-status', logEntry.severity);
    logLineContent.textContent = logEntry.message;

    return logLine;
  };

  #scrollToBottom = () => {
    this.#logsScroll.scrollTop = this.#logsListHeight;
    this.refreshView();
  };

  refreshView = () => {
    const scrollTop = this.#logsScroll.scrollTop;
    const viewportHeight = this.#logsScroll.clientHeight;

    // calc vision limit with buffer
    const startBoundary = scrollTop - this.#bufferSize * viewportHeight;
    const endBoundary = scrollTop + viewportHeight + this.#bufferSize * viewportHeight;

    // search index of visible logs
    const visibleLogs: Log[] = [];
    let marginTop = 0;
    let marginBottom = 0;

    for (const [id, offsetTop] of this.#logsIndexHeight.entries()) {
      const log = logStore.getLogs()[id];
      const logHeight = log.height;

      if (offsetTop + logHeight < startBoundary) {
        marginTop += logHeight;
      } else if (offsetTop > endBoundary) {
        marginBottom += logHeight;
      } else {
        visibleLogs.push(log);
      }
    }

    this.#logsList.style.marginTop = `${marginTop}px`;
    this.#logsList.style.marginBottom = `${marginBottom}px`;

    this.#logsList.innerHTML = '';
    visibleLogs.forEach((log) => {
      if (log.HTMLElement) {
        this.#logsList.appendChild(log.HTMLElement);
      }
    });
  };

  #debouncedRefreshView = debounce(() => {
    this.refreshView();
  }, this.#debounceTime);

  /**
   * @public
   * @description Displays a summary of logs, grouping warnings and errors.
   * Summaries include links to the corresponding log lines.
   * Adds a click event listener to handle navigation within the summary.
   * Prevents displaying a summary if no logs are present.
   */
  public displaySummary = async (): Promise<void> => {
    if (logStore.getLogsLength() === 0) {
      console.warn('Cannot display summary because logs are empty');
      return;
    }

    const fragment = document.createDocumentFragment();

    const warnings = logStore.getWarnings();
    if (warnings.length > 0) {
      const warningsSummary = this.#createSummary(Severity.WARNING, warnings);
      fragment.appendChild(warningsSummary);
    }

    const errors = logStore.getErrors();
    if (errors.length > 0) {
      const errorsSummary = this.#createSummary(Severity.ERROR, errors);
      fragment.appendChild(errorsSummary);
    }

    if (fragment.hasChildNodes()) {
      this.#logsSummary.addEventListener('click', this.#handleLinkEvent);
    }

    this.#logsSummary.appendChild(fragment);

    // await api.post(this.element.dataset.downloadLogsRoute!);
    //
    // this.#appendFragmentElement(fragment, this.#logsSummary);
    // this.#isSummaryDisplayed = true;
  };

  /**
   * @private
   * @param {Severity} severity - The severity type (e.g., warning, error).
   * @param {Log[]} logs - Array of logs to include in the summary.
   * @returns {HTMLDivElement} - The created summary element.
   * @description Creates a summary element grouping logs by severity.
   * Each log in the summary includes a link to its corresponding log line.
   */
  #createSummary(severity: Severity, logs: Log[]): HTMLDivElement {
    const summaryFragment = this.#templateSummary.content.cloneNode(true) as DocumentFragment;

    const summary = summaryFragment.querySelector('.logs__summary') as HTMLDivElement;
    summary.setAttribute('data-summary-severity', severity);

    const summaryScroll = summaryFragment.querySelector('.logs__summary-scroll') as HTMLDivElement;

    const title = this.#getSummaryTitle(severity);
    const titleContainer = summary.querySelector('[data-slot-template="title"]') as HTMLDivElement;
    titleContainer.textContent = title;

    const countContainer = summary.querySelector('[data-slot-template="count"]') as HTMLDivElement;
    countContainer.textContent = String(logs.length);

    const linkElement = this.#createSummaryLinkElement(severity);

    logs.forEach((log) => {
      const logElement = this.#createSummaryLogLine(log);
      const linkClone = linkElement.cloneNode(true) as HTMLAnchorElement;
      linkClone.href = `#${String(log.offsetTop)}`;

      logElement.appendChild(linkClone);

      summaryScroll.appendChild(logElement);
    });

    return summary;
  }

  /**
   * @private
   * @param {Severity} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {string} - The content of the title template.
   * @description Retrieves the title template for the given severity type and extracts its content.
   */
  #getSummaryTitle(severity: Severity): string {
    const titleTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-title`,
      `Summary ${severity} title not found`
    );

    const title = titleTemplate.content.cloneNode(true) as HTMLElement;

    return title.textContent!;
  }

  /**
   * @private
   * @param {Severity} severity - The severity type (e.g., WARNING, ERROR).
   * @returns {HTMLAnchorElement} - The created link element.
   * @description Creates a link element from the template corresponding to the given severity type.
   */
  #createSummaryLinkElement(severity: Severity): HTMLAnchorElement {
    const linkTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-link`,
      `Summary ${severity} link not found`
    );

    const linkFragment = linkTemplate.content.cloneNode(true) as DocumentFragment;
    return linkFragment.querySelector('.link') as HTMLAnchorElement;
  }

  /**
   * @private
   * @param {MouseEvent} event - The click event object.
   * @description Handles click events on summary links to scroll to the corresponding log line.
   * Highlights the target log line briefly for visual focus.
   */
  #handleLinkEvent = (event: MouseEvent): void => {
    const target = event.target as HTMLAnchorElement;

    // Checks if the clicked element is an <a> tag pointing towards an ID
    if (!target || target.tagName !== 'A' || !target.hash) {
      return;
    }

    event.preventDefault();

    const offsetTopToScroll = target.hash.substring(1);
    const logKey = [...this.#logsIndexHeight.entries()].find(
      ([key, value]) => value === Number(offsetTopToScroll)
    )?.[0];

    if (logKey === undefined) {
      return;
    }

    const HTMLElement = logStore.getLog(logKey).HTMLElement;

    if (HTMLElement === undefined) {
      return;
    }

    this.#logsScroll.scrollTop = Number(offsetTopToScroll);
    window.setTimeout(() => {
      HTMLElement.classList.add('logs__line--pointed');
    }, 100);
    window.setTimeout(() => {
      HTMLElement.classList.remove('logs__line--pointed');
    }, 2000);
  };
}

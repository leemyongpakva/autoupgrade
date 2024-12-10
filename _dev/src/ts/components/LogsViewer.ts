import ComponentAbstract from './ComponentAbstract';
import { LogEntry, Log } from '../types/logsTypes';
import { parseLogWithSeverity, debounce } from '../utils/logsUtils';
import DomLifecycle from '../types/DomLifecycle';

export default class LogsViewer extends ComponentAbstract implements DomLifecycle {
  #logs: Log[] = [];
  #logsIndexHeight: Map<number, number> = new Map();
  #logsListHeight: number = this.#logsList.clientHeight;
  #bufferSize = 1;

  get #logsScroll() {
    return this.queryElement<HTMLDivElement>(
      '[data-slot-component="scroll"]',
      'Logs scroll not found'
    );
  }

  get #logsList() {
    return this.queryElement<HTMLDivElement>('[data-slot-component="list"]', 'Logs list not found');
  }

  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );

  public mount = () => {
    this.#logsScroll.addEventListener('scroll', this.#debouncedRefreshView);
  };

  public beforeDestroy = () => {
    this.#logsScroll.removeEventListener('scroll', this.#debouncedRefreshView);
  };

  public addLogs = (logs: string[]): void => {
    let count = 0;

    logs.forEach((log) => {
      const id = this.#logs.length;
      const logEntry = parseLogWithSeverity(log);
      const HTMLElement = this.#createLogLine(logEntry);
      this.#logsList.appendChild(HTMLElement);

      const height = HTMLElement.offsetHeight;
      const offsetTop = HTMLElement.offsetTop;

      this.#logs.push({
        ...logEntry,
        height,
        offsetTop,
        HTMLElement
      });

      this.#logsIndexHeight.set(id, offsetTop);
      this.#logsListHeight += height;

      count += 1;

      if (count > 20) {
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
    let firstVisibleIndex = 0;
    let lastVisibleIndex = this.#logs.length - 1;

    for (const [id, offsetTop] of this.#logsIndexHeight) {
      const logHeight = this.#logs[id].height;

      if (offsetTop + logHeight >= startBoundary && firstVisibleIndex === 0) {
        firstVisibleIndex = id;
      }

      if (offsetTop <= endBoundary) {
        lastVisibleIndex = id;
      } else {
        break;
      }
    }

    // delete logs outsite view
    this.#logs.forEach((log, index) => {
      if (index < firstVisibleIndex || index > lastVisibleIndex) {
        if (log.HTMLElement?.parentElement) {
          log.HTMLElement.remove();
        }
      } else if (log?.HTMLElement?.parentElement) {
        // add logs not present inside dom
        this.#logsList.appendChild(log.HTMLElement);
      }
    });

    // calc margin to preserve list height
    const marginTop = this.#logsIndexHeight.get(firstVisibleIndex) || 0;
    const marginBottom =
      this.#logsListHeight -
      ((this.#logsIndexHeight.get(lastVisibleIndex) || 0) + this.#logs[lastVisibleIndex].height);

    this.#logsList.style.marginTop = `${marginTop}px`;
    this.#logsList.style.marginBottom = `${marginBottom}px`;
  };

  #debouncedRefreshView = debounce(() => {
    this.refreshView();
  }, 500);
}

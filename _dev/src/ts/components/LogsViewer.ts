import { ComponentAbstract } from './ComponentAbstract';
import { SeverityClasses, LogEntry } from '../types/logsTypes';
import { parseLogWithSeverity } from '../utils/logsUtils';

export class LogsViewer extends ComponentAbstract {
  #warnings: string[] = [];
  #errors: string[] = [];
  #isSummaryDisplayed: boolean = false;

  #logsList = this.queryElement<HTMLDivElement>(
    '[data-slot-component="list"]',
    'Logs list not found'
  );
  #logsScroll = this.queryElement<HTMLDivElement>(
    '[data-slot-component="scroll"]',
    'Logs scroll not found'
  );
  #logsSummary = this.queryElement<HTMLDivElement>(
    '[data-slot-component="summary"]',
    'Logs summary not found'
  );
  #templateLogLine = this.queryElement<HTMLTemplateElement>(
    '#log-line',
    'Template log line not found'
  );
  #templateSummary = this.queryElement<HTMLTemplateElement>(
    '#log-summary',
    'Template summary not found'
  );

  public addLogs = (logs: string[]): void => {
    if (this.#isSummaryDisplayed) {
      console.warn('Cannot add logs while the summary is displayed');
      return;
    }

    const fragment = document.createDocumentFragment();

    logs.forEach((log) => {
      const logEntry = parseLogWithSeverity(log);
      const logLine = this.#createLogLine(logEntry);

      if (logEntry.className === SeverityClasses.WARNING) {
        const id = `warning-${this.#warnings.length}`;
        this.#warnings.push(id);
        logLine.id = id;
      }

      if (logEntry.className === SeverityClasses.ERROR) {
        const id = `error-${this.#errors.length}`;
        this.#errors.push(id);
        logLine.id = id;
      }

      fragment.appendChild(logLine);
    });

    this.#appendFragmentElement(fragment, this.#logsList);
    this.#scrollToBottom();
  };

  #createLogLine = (logEntry: LogEntry): HTMLDivElement => {
    const logLineFragment = this.#templateLogLine.content.cloneNode(true) as DocumentFragment;
    const logLine = logLineFragment.querySelector('.logs__line') as HTMLDivElement;

    logLine.classList.add(`logs__line--${logEntry.className}`);
    logLine.setAttribute('data-status', logEntry.className);
    logLine.textContent = logEntry.message;

    return logLine;
  };

  #appendFragmentElement = (fragment: DocumentFragment, element: HTMLElement) => {
    element.appendChild(fragment);
  };

  #scrollToBottom = () => {
    this.#logsScroll.scrollTop = this.#logsScroll.scrollHeight;
  };

  public displaySummary(): void {
    if (!this.#logsList.hasChildNodes()) {
      console.warn('Cannot display summary because logs are empty');
      return;
    }

    const fragment = document.createDocumentFragment();

    if (this.#warnings.length > 0) {
      const warningsSummary = this.#createSummary(SeverityClasses.WARNING, this.#warnings);
      fragment.appendChild(warningsSummary);
    }

    if (this.#errors.length > 0) {
      const errorsSummary = this.#createSummary(SeverityClasses.ERROR, this.#errors);
      fragment.appendChild(errorsSummary);
    }

    if (fragment.hasChildNodes()) {
      this.#logsSummary.addEventListener('click', this.#handleLinkEvent);
    }

    this.#appendFragmentElement(fragment, this.#logsSummary);
    this.#isSummaryDisplayed = true;
  }

  #createSummary(severity: SeverityClasses, logs: string[]): HTMLDivElement {
    const summaryFragment = this.#templateSummary.content.cloneNode(true) as DocumentFragment;
    const summary = summaryFragment.querySelector('.logs__summary') as HTMLDivElement;

    const title = this.#getSummaryTitle(severity);
    const titleContainer = summary.querySelector('[data-slot-template="title"]') as HTMLDivElement;
    titleContainer.textContent = title;

    const linkElement = this.#createSummaryLinkElement(severity);

    logs.forEach((logId) => {
      const logElement = document.getElementById(logId)!;
      const cloneLogElement = logElement.cloneNode(true) as HTMLDivElement;
      cloneLogElement.id = '';

      const linkClone = linkElement.cloneNode(true) as HTMLAnchorElement;
      linkClone.href = `#${logId}`;

      cloneLogElement.appendChild(linkClone);

      summary.appendChild(cloneLogElement);
    });

    return summary;
  }

  #getSummaryTitle(severity: SeverityClasses): string {
    const titleTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-title`,
      `Summary ${severity} title not found`
    );

    const title = titleTemplate.content.cloneNode(true) as HTMLElement;

    return title.textContent!;
  }

  #createSummaryLinkElement(severity: SeverityClasses): HTMLAnchorElement {
    const linkTemplate = this.queryElement<HTMLTemplateElement>(
      `#summary-${severity}-link`,
      `Summary ${severity} link not found`
    );

    const linkFragment = linkTemplate.content.cloneNode(true) as DocumentFragment;
    return linkFragment.querySelector('.link') as HTMLAnchorElement;
  }

  #handleLinkEvent = (event: MouseEvent): void => {
    const target = event.target as HTMLAnchorElement;

    if (!target || target.tagName !== 'A' || !target.hash) {
      return;
    }

    event.preventDefault();

    const logId = target.hash.substring(1);
    const targetElement = document.getElementById(logId);

    if (targetElement) {
      const scrollTop = targetElement.offsetTop - this.#logsScroll.offsetTop;
      this.#logsScroll.scrollTop = scrollTop;
      targetElement.classList.add('logs__pointed');
      window.setTimeout(() => {
        targetElement.classList.remove('logs__pointed');
      }, 2000);
    }
  };
}

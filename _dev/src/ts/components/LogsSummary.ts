import { ComponentAbstract } from './ComponentAbstract';

export class LogsSummary extends ComponentAbstract {
  get #logsSummaryText(): HTMLDivElement {
    const logsSummaryText = this.element.querySelector('#logs_summary_text') as HTMLDivElement;
    if (!logsSummaryText) throw new Error('Logs summary text not found');

    return logsSummaryText;
  }

  /**
   * @public
   * @param text - text summary to display.
   * @description Allows to update the summary text of the logs.
   */
  public setLogsSummaryText = (text: string): void => {
    this.#logsSummaryText.innerText = text;
  };
}

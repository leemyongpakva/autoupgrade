import { ComponentAbstract } from './ComponentAbstract';

export class ProgressBar extends ComponentAbstract {
  get #progressBar(): HTMLDivElement {
    const progressBar = this.element.querySelector('[role="progressbar"]') as HTMLDivElement;
    if (!progressBar) throw new Error('Progress bar not found');

    return progressBar;
  }

  /**
   * @private
   * @param percentage - Un nombre entre 0 et 100 représentant le pourcentage de progression.
   * @description Update all progress bar attribute from percentage given.
   */
  public setProgressPercentage = (percentage: number) => {
    const progressPercentage = Number(percentage).toString();
    const progressBar = this.#progressBar;

    progressBar.style.width = `${progressPercentage}%`;
    progressBar.setAttribute('aria-valuenow', progressPercentage);

    const titleTemplate = progressBar.dataset.titleTemplate;
    if (titleTemplate) {
      const formattedTitle = this.#formatTitle(titleTemplate, progressPercentage);
      progressBar.title = formattedTitle;
      progressBar.setAttribute('aria-label', formattedTitle);
    } else {
      console.warn('Title template not found on progress bar');
    }
  };

  /**
   * @param {string} template - The title template containing "{progress_percentage}".
   * @param {string} percentage - The progress percentage as a string.
   * @returns string - The formatted title.
   * @description Replaces "{progress_percentage}" in the template with the given percentage.
   */
  #formatTitle(template: string, percentage: string): string {
    return template.replace('{progress_percentage}', percentage);
  }
}

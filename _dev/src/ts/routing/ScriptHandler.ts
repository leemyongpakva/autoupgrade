import HomePage from '../pages/HomePage';
import UpdatePageVersionChoice from '../pages/UpdatePageVersionChoice';
import UpdatePageUpdateOptions from '../pages/UpdatePageUpdateOptions';
import UpdatePageBackup from '../pages/UpdatePageBackup';
import UpdatePageUpdate from '../pages/UpdatePageUpdate';
import UpdatePagePostUpdate from '../pages/UpdatePagePostUpdate';
import PageAbstract from '../pages/PageAbstract';
import { RoutesMatching } from '../types/scriptHandlerTypes';
import { routeHandler } from '../autoUpgrade';

export default class ScriptHandler {
  #currentScript: PageAbstract | undefined;

  /**
   * @private
   * @type {RoutesMatching}
   * @description Maps route names to their corresponding page classes.
   */
  readonly #routesMatching: RoutesMatching = {
    'home-page': HomePage,
    'update-page-version-choice': UpdatePageVersionChoice,
    'update-page-update-options': UpdatePageUpdateOptions,
    'update-page-backup': UpdatePageBackup,
    'update-page-update': UpdatePageUpdate,
    'update-page-post-update': UpdatePagePostUpdate
  };

  /**
   * @constructor
   * @description Initializes the `ScriptHandler` by loading the page script associated with the current route.
   */
  constructor() {
    const currentRoute = routeHandler.getCurrentRoute();

    if (currentRoute) {
      this.#loadScript(currentRoute);
    }
  }

  /**
   * @private
   * @param {string} routeName - The name of the route to load his associated script.
   * @returns void
   * @description Loads and mounts the page script associated with the specified route name.
   */
  #loadScript(routeName: string) {
    const pageClass = this.#routesMatching[routeName];
    if (pageClass) {
      try {
        this.#currentScript = new pageClass();
        this.#currentScript.mount();
      } catch (error) {
        console.error(`Failed to load script for route ${routeName}:`, error);
      }
    } else {
      console.debug(`No matching page Class found for route: ${routeName}`);
    }
  }

  /**
   * @public
   * @param {string} newRoute - The name of the route to load his associated script.
   * @returns void
   * @description Updates the currently loaded route script by destroying the current
   *              page instance and loading a new one based on the provided route name.
   */
  public updateRouteScript(newRoute: string) {
    this.#currentScript?.beforeDestroy();
    this.#loadScript(newRoute);
  }

  /**
   * @public
   * @returns void
   * @description Unloads the currently loaded script.
   *  Should be called before updating the DOM.
   */
  public unloadRouteScript(): void {
    this.#currentScript?.beforeDestroy();
    this.#currentScript = undefined;
  }
}

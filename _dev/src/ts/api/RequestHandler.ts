import baseApi from './baseApi';
import { ApiResponse, ApiResponseAction } from '../types/apiTypes';
import Hydration from '../utils/Hydration';

export class RequestHandler {
  private currentRequestAbortController: AbortController | null = null;

  /**
   * @public
   * @param {string} route - Target route for the POST request.
   * @param {FormData}[data=new FormData()] - Form data to send with the request by default we send FormData with admin dir required by backend.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<ApiResponseAction | void>}
   * @description Sends a POST request to the specified route with optional data and pop state indicator. Cancels any ongoing request before initiating a new one.
   */
  public async post(
    route: string,
    data: FormData = new FormData(),
    fromPopState?: boolean
  ): Promise<void> {
    // Cancel any previous request if it exists
    if (this.currentRequestAbortController) {
      this.currentRequestAbortController.abort();
    }

    // Create a new AbortController for the current request (used to cancel previous request)
    this.currentRequestAbortController = new AbortController();
    const { signal } = this.currentRequestAbortController;

    // Append admin dir required by backend
    data.append('dir', window.AutoUpgradeVariables.admin_dir);

    try {
      const response = await baseApi.post('', data, {
        params: { route },
        signal
      });

      const responseData = response.data as ApiResponse;
      await this.#handleResponse(responseData, fromPopState);
    } catch (error) {
      // TODO: catch errors
      console.error(error);
    }
  }

  public async postAction(action: string): Promise<ApiResponseAction | void> {
    const data = new FormData();

    data.append('dir', window.AutoUpgradeVariables.admin_dir);
    data.append('action', action);

    try {
      const response = await baseApi.post('', data);
      return response.data as ApiResponseAction;
    } catch (error) {
      // TODO: catch errors
      console.error(error);
    }
  }

  /**
   * @private
   * @param {ApiResponse} response - The response data from the API.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<void>}
   * @description Handles the API response by checking for next route or hydration data.
   */
  async #handleResponse(response: ApiResponse, fromPopState?: boolean): Promise<void> {
    if ('next_route' in response) {
      await this.post(response.next_route);
    }
    if ('hydration' in response) {
      new Hydration().hydrate(response, fromPopState);
    }
  }
}

const api = new RequestHandler();

export default api;

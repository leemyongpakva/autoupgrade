import baseApi from './baseApi';
import { ApiResponse } from '../types/apiTypes';
import Hydration from '../utils/Hydration';

export class RequestHandler {
  private currentRequestAbortController: AbortController | null = null;

  public async post(route: string, data = new FormData(), fromPopState?: boolean) {
    if (this.currentRequestAbortController) {
      this.currentRequestAbortController.abort();
    }

    this.currentRequestAbortController = new AbortController();
    const { signal } = this.currentRequestAbortController;

    data.append('dir', window.AutoUpgradeVariables.admin_dir);

    try {
      const response = await baseApi.post('', data, {
        params: { route },
        signal
      });

      const responseData = response.data as ApiResponse;
      this.handleResponse(responseData, fromPopState);
    } catch (error) {
      // TODO: catch errors
      console.error(error);
    }
  }

  private handleResponse(response: ApiResponse, fromPopState?: boolean) {
    if ('next_route' in response) {
      this.post(response.next_route);
    }
    if ('hydration' in response) {
      new Hydration().hydrate(response, fromPopState);
    }
  }
}

const api = new RequestHandler();

export default api;

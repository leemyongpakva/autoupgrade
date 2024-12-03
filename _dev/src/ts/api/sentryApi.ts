import * as Sentry from '@sentry/browser';
import { SeverityLevel } from '@sentry/browser';
import { maskSensitiveInfoInUrl } from '../utils/urlUtils';

Sentry.init({
  dsn: 'https://eae192966a8d79509154c65c317a7e5d@o298402.ingest.us.sentry.io/4507254110552064',
  release: `v${window.AutoUpgradeVariables.module_version}`,
  sendDefaultPii: false,
  beforeSend(event) {
    if (event.type === 'session') {
      return null;
    }

    if (event.request?.url) {
      event.request.url = maskSensitiveInfoInUrl(
        window.location.href,
        window.AutoUpgradeVariables.admin_dir
      );
    }

    return event;
  },
  beforeBreadcrumb(breadcrumb) {
    if (breadcrumb.data?.url) {
      breadcrumb.data.url = maskSensitiveInfoInUrl(
        breadcrumb.data.url,
        window.AutoUpgradeVariables.admin_dir
      );
    }

    if (breadcrumb.data?.from) {
      breadcrumb.data.from = maskSensitiveInfoInUrl(
        breadcrumb.data.from,
        window.AutoUpgradeVariables.admin_dir
      );
    }

    if (breadcrumb.data?.to) {
      breadcrumb.data.to = maskSensitiveInfoInUrl(
        breadcrumb.data.to,
        window.AutoUpgradeVariables.admin_dir
      );
    }

    return breadcrumb;
  }
});

export function sendUserFeedback(
  message: string,
  logs: { logs?: string; warnings?: string; errors?: string } = {},
  feedback: { email?: string; comments?: string } = {},
  level: SeverityLevel = 'error'
) {
  if (logs.logs) {
    Sentry.getCurrentScope().addAttachment({
      filename: 'logs.txt',
      data: logs.logs,
      contentType: 'text/plain'
    });
  }

  if (logs.warnings) {
    Sentry.getCurrentScope().addAttachment({
      filename: 'summary_warnings.txt',
      data: logs.warnings,
      contentType: 'text/plain'
    });
  }

  if (logs.errors) {
    Sentry.getCurrentScope().addAttachment({
      filename: 'summary_errors.txt',
      data: logs.errors,
      contentType: 'text/plain'
    });
  }

  const maskedUrl = maskSensitiveInfoInUrl(
    window.location.href,
    window.AutoUpgradeVariables.admin_dir
  );

  const eventId = Sentry.captureEvent({
    message,
    level,
    tags: {
      url: maskedUrl
    }
  });

  if (feedback.email || feedback.comments) {
    Sentry.captureFeedback({
      associatedEventId: eventId,
      email: feedback.email || '',
      message: feedback.comments || '',
      url: maskedUrl
    });
  }

  Sentry.getCurrentScope().clearAttachments();
}

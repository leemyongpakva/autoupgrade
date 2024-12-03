export function maskSensitiveInfoInUrl(url: string, adminFolder: string): string {
  const placeHolder = '********';
  const adminFolderRegex = new RegExp(adminFolder, 'g');
  const maskedUrl = url.replace(adminFolderRegex, placeHolder);

  const tokenRegex = new RegExp('&token=[^&]*', 'gi');
  return maskedUrl.replace(tokenRegex, `&token=${placeHolder}`);
}

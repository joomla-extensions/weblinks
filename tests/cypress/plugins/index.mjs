import { getMails, clearEmails, startMailServer } from './mail.mjs';
import { writeRelativeFile, deleteRelativePath, copyRelativeFile, getRelativeFileSha512} from './fs.mjs';
import { queryTestDB, deleteInsertedItems } from './db.mjs';
import { checkForLogs, clearLogs } from './logs.mjs';

/**
 * Does the setup of the plugins.
 *
 * @param {*} on
 * @param {object} config The configuration
 *
 * @see https://docs.cypress.io/guides/references/configuration#setupNodeEvents
 */
export default function setupPlugins(on, config) {
  on('task', {
    queryDB: (query) => queryTestDB(query, config),
    cleanupDB: () => deleteInsertedItems(config),
    writeRelativeFile: ({ path, content, mode }) => writeRelativeFile(path, content, config, mode),
    deleteRelativePath: (path) => deleteRelativePath(path, config),
    copyRelativeFile: ({ source, destination }) => copyRelativeFile(source, destination, config),
	getRelativeFileSha512: (relativePath) => getRelativeFileSha512(relativePath, config),
    checkForLogs: () => checkForLogs(config),
    clearLogs: () => clearLogs(config),
    getMails: () => getMails(),
    clearEmails: () => clearEmails(),
    startMailServer: () => startMailServer(config),
    getLatestReleaseZipUrl: async () => {
      const headers = {
        Accept: 'application/vnd.github+json',
        'X-GitHub-Api-Version': '2022-11-28',
        'User-Agent': 'weblinks-cypress-tests',
      };

      if (process.env.GITHUB_TOKEN) {
        headers.Authorization = `Bearer ${process.env.GITHUB_TOKEN}`;
      }

      const response = await fetch(
        'https://api.github.com/repos/joomla-extensions/weblinks/releases/latest',
        { headers },
      );
      const release = await response.json();

      if (!response.ok) {
        throw new Error(
          `GitHub releases/latest returned ${response.status}: ${release.message || 'unknown error'}`,
        );
      }

      const zipAsset = (release.assets || []).find(
        (asset) => /^pkg[-_]weblinks.*\.zip$/i.test(asset.name),
      );

      if (!zipAsset) {
        throw new Error(
          `Latest package ZIP not found. Assets: ${(release.assets || []).map((asset) => asset.name).join(', ') || 'none'}`,
        );
      }

      return zipAsset.browser_download_url;
    },
  });
}


import { defineConfig, devices } from '@playwright/test'

const isContinuousIntegration = Boolean(process.env.CI)
const externalBaseUrl = process.env.PLAYWRIGHT_BASE_URL
const baseURL = externalBaseUrl ?? 'http://127.0.0.1:8000'
const vitePort = process.env.VITE_PORT ?? '5173'

export default defineConfig({
    testDir: './e2e',
    fullyParallel: true,
    forbidOnly: isContinuousIntegration,
    retries: isContinuousIntegration ? 2 : 0,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
        },
        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'] },
        },
    ],
    webServer: externalBaseUrl
        ? undefined
        : [
              {
                  name: 'Laravel',
                  command:
                      'PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=127.0.0.1 --port=8000 --no-reload',
                  url: `${baseURL}/up`,
                  reuseExistingServer: !isContinuousIntegration,
                  timeout: 120_000,
              },
              {
                  name: 'Vite',
                  command: `npm run dev -- --host 127.0.0.1 --port ${vitePort} --strictPort`,
                  url: `http://127.0.0.1:${vitePort}/@vite/client`,
                  gracefulShutdown: {
                      signal: 'SIGTERM',
                      timeout: 5_000,
                  },
                  reuseExistingServer: !isContinuousIntegration,
                  timeout: 120_000,
              },
          ],
})

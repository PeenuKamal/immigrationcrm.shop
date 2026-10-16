// PM2 process file:  pm2 start ecosystem.config.cjs
module.exports = {
  apps: [
    {
      name: "immigrationcrm",
      script: "node_modules/next/dist/bin/next",
      args: "start -p 3000",
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      max_memory_restart: "700M",
      env: { NODE_ENV: "production" },
    },
  ],
};

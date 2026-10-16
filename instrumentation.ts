// Next.js runs this once when the server starts.
export async function register() {
  if (process.env.NEXT_RUNTIME === "nodejs") {
    const { setupDatabase } = await import("./db/setup");
    await setupDatabase();
  }
}

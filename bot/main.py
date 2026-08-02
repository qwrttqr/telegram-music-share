import asyncio
import os
import sys
from aiogram import Bot, Dispatcher, html
from aiogram.client.default import DefaultBotProperties
from aiogram.enums import ParseMode
from aiogram.types import Message
from dotenv import load_dotenv

load_dotenv()

TOKEN = os.getenv("BOT_TOKEN")
if not TOKEN:
    sys.exit("Error: BOT_TOKEN environment variable is missing.")

dp = Dispatcher()


@dp.message()
def echo_handler(message: Message) -> None:
    """Simple echo handler to verify the bot works."""
    message.answer(f"Hello, {html.bold(message.from_user.full_name)}!")


async def main() -> None:
    # Initialize Bot instance with default parse mode
    bot = Bot(token=TOKEN, default=DefaultBotProperties(parse_mode=ParseMode.HTML))

    print("Bot is starting...")
    # Start polling and skip updates that arrived while the bot was offline
    await bot.delete_webhook(drop_pending_updates=True)
    await dp.start_polling(bot)


if __name__ == "__main__":
    asyncio.run(main())

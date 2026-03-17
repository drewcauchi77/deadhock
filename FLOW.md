1. Discord initially verifies that /interactions endpoint exists. There needs to be middleware and type == 1 to be sent back as confirmation
2. When the bot is added to a channel, nothing is sent to the app as a request
3. The command needs to be run either for dev mode or else globally so that the discord hook is added
4. When the hook is added in discord, and it is used, then a request is sent which needs to be handled.

# local_ai_manager - Multi-tenant AI backend

This plugin provides a fully functional AI backend. It can be used as alternative choice for the AI subsystem which is being shipped with Moodle 4.5. The AI manager provides the key feature of being a _**multi-tenant**_ AI backend which is the main reason you should be using this plugin for providing AI functionality in your moodle instance instead of the moodle core AI subsystem.

CARE: Currently, plugins requiring the moodle AI subsystem are not compatible with the AI manager and vice versa. 

## Features

Key features are:

- Multi-tenancy
- Two subplugin types
    - *AI tool* (Namespace prefix `aitool`): A subplugin of this type basically connects an external AI tool like ChatGPT, Google Gemini, Dall-E, Ollama etc. to the moodle instance.
    - *AI purpose* (Namespace prefix `aipurpose`): This subplugin defines a purpose which is being used. For each purpose which is being used by frontend plugins you can define a different AI tool to be used, and you can even differentiate for different roles.
- Customizability due to the provisioning of hooks that can be used to customize the
handling of the tenants in your moodle instance
- API functions both on PHP and JS side for connecting frontend plugins like:
  - Chatbot (https://github.com/mebis-lp/moodle-block_ai_chat)
  - AI text question type (https://github.com/mebis-lp/moodle-qtype_aitext which is a fork of https://github.com/marcusgreen/moodle-qtype_aitext)
  - Tiny AI tools (https://github.com/mebis-lp/moodle-tiny_ai)
  - ... probably more to come
- Enabling/disabling of users of a tenant
- Limiting requests per time for each role
- Control if users with a certain role can access certain purposes
- Detailed possibilities to monitor user usage

Currently supported AI tools:
- OpenAI ChatGPT (also via Azure)
- OpenAI TTS (also via Azure)
- OpenAI Dall-E (also via Azure)
- Google Gemini
- Google Synthesize (text to speech)
- Ollama

Currently available AI purposes:
- Chat
- Feedback
- Image generation
- Image to text
- Single prompt
- Translate
- Text to speech


## Requirements

As this plugin makes heavy usage of the hooks and dependency injection, you need at least Moodle 4.4 for running this plugin.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/ai_manager

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2024, ISB Bayern

Lead developer: Philipp Memmel <philipp.memmel@isb.bayern.de>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.

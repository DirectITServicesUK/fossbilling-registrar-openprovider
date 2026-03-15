# OpenProvider Integration for FOSSBilling

This project integrates the OpenProvider domain registrar with FOSSBilling, enabling users to manage domain registration, transfer, and renewal directly from their FOSSBilling platform.

---

## Features

- **Domain Registration**: Register new domains using OpenProvider's API.
- **Domain Transfer**: Transfer existing domains to OpenProvider from FOSSBilling.
- **Domain Management**: Update DNS, WHOIS, and other settings directly.
- **Renewals**: Automate domain renewals through OpenProvider.

### Additions by [Direct IT Services](https://www.directitservices.co.uk)

- **Nominet .uk IPS Tag Transfer Support**: `.uk`, `.co.uk`, `.org.uk`, `.me.uk` and other Nominet TLDs now use the correct IPS tag transfer mechanism instead of EPP auth codes. OpenProvider's IPS tag is `REGISTRAR-EU`.
- **Minimum Period Enforcement**: TLDs with minimum registration periods greater than 1 year (e.g. `.ai` requires 2 years) are automatically enforced at the adapter level.
- **Transfer Order Form Template Override**: An updated `mod_servicedomain_order_form.html.twig` is included that conditionally shows IPS tag instructions for `.uk` domains instead of the EPP auth code field. Copy this to your FOSSBilling theme to apply.

---

## Requirements

- **FOSSBilling**: Make sure you have FOSSBilling installed and properly configured.
- **OpenProvider Account**: An active account with OpenProvider is required to use their API.

---

## Installation

1. Clone this repository and copy the files to the root of your FOSSBilling installation:
   ```bash
   git clone https://github.com/DirectITServicesUK/fossbilling-registrar-openprovider.git
   ```
1. Navigate to the FOSSBilling admin panel.

1. Go to System > Domain registration > New domain registrar and enable the OpenProvider module.

1. Refresh the page, go to the Registrars tab and edit the OpenProvider settings

1. Enter your OpenProvider API credentials:
   - API URL: Live https://api.openprovider.eu (Sandbox http://api.sandbox.openprovider.nl:8480)
   - Username
   - Password
1. Save your configuration.

## Usage

1. Add OpenProvider as your registrar for specific TLDs in FOSSBilling.

1. Clients can register, transfer, or renew domains through your billing system, and the integration will communicate with OpenProvider's API to process requests.

1. Monitor and manage domain actions directly from your FOSSBilling admin panel.

## Troubleshooting

- Connection Issues: Ensure your server can connect to the OpenProvider API endpoint.
- API Errors: Double-check your credentials and ensure your OpenProvider account has sufficient privileges.
- PHP Errors: Verify if your PHP version is supported by FOSSBilling.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
1. Create a new branch (feature/your-feature).
1. Commit your changes.
1. Open a pull request with a detailed description.

## License

This project is licensed under the Apache 2.0 License. See the [LICENSE](LICENSE) file for details.

## Acknowledgments

1. [OpenProvider](https://www.openprovider.com/) for their robust API.
1. [FOSSBilling](https://fossbilling.org/) for their open-source billing platform.
1. [Devife](https://www.devife.com/) for the original OpenProvider registrar module.
1. [Direct IT Services](https://www.directitservices.co.uk/) for .uk IPS tag transfer support and TLD minimum period handling.

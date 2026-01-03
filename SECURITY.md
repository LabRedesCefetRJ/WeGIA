# Security Policy

## 🔐 How to Contribute to WeGIA's Security

You can help improve the security of the **WeGIA** project by analyzing the code during the design phase, running a local instance on your computer, or using the public test server.

🚨Please do not submit vulnerabilities through other means like VulnDB plataform. Our vulnerability disclosure policy is fully centered on GitHub Advisory.🚨


---

### 🧠 Design-Time Analysis

To test WeGIA’s code during the design phase, clone the repository and use static analysis tools. Here are some suggestions:

- **mn-analise**
  - Read the whitepaper: [An extension for VSCode that uses ChatGPT as a tool to support secure software development](https://periodicos.univali.br/index.php/acotb/article/view/20376)
  - Available on the [Visual Studio Marketplace](https://marketplace.visualstudio.com/items/MustafaNeto.mn-analise/)

---

### 🖥️ Runtime Testing (Local Instance)

You can use a virtual machine with WeGIA pre-installed to run your security tests.

- **VirtualBox**
  - Prerequisite: Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
  - Download the [WeGIA Virtual Machine](https://www.wegia.org/vm/)
    - **Username**: `wegia`  
    - **Password**: `wegia`
  - Watch the [WeGIA VM tutorial on YouTube](https://youtu.be/mGayZb2snqk)

- **Local Installation**

  Follow the [ installation instructions](https://github.com/LabRedesCefetRJ/WeGIA?tab=readme-ov-file#como-instalar)

---

### 🌐 Runtime Testing (Public Server)

You can use a public server with WeGIA pre-installed to run your security tests.

- [Security Testing Server](https://sec.wegia.org/)

---

## 📦 Supported Versions

The following table indicates which versions of WeGIA receive security updates:

| Version | Supported |
|---------|-----------|
| ≥ 3.6   | ✅ Yes     |
| < 3.6   | ❌ No      |

> Only versions 3.4 and above are actively maintained for security.

---

## 🛡️ Reporting a Vulnerability

If you discover a security vulnerability in WeGIA, we encourage responsible disclosure.

- **Preferred method:** Submit a private advisory via GitHub.
- **GitHub Security Advisory:** [https://github.com/LabRedesCefetRJ/WeGIA/security/advisories](https://github.com/LabRedesCefetRJ/WeGIA/security/advisories)
- **Alternative contact:** Send an email to `labredes@grupo.cefet-rj.br`

Please include the following details if possible:

- Description of the issue
- Steps to reproduce
- Affected version(s)
- Potential impact

We aim to respond within **72 business hours**.

---

**Thank you for helping to keep WeGIA secure.**

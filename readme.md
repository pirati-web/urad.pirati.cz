# urad.pirati.cz

## Zakládání firem online

Webová aplikace sloužící k zakládání s.r.o. online. Je nasazena na webu [urad.pirati.cz](https://urad.pirati.cz/) a jejím primárním účelem je podpora pirátského návrhu zákona na zjednodušení zakládání firem [Firma za 1 den](https://www.profant.eu/2019/firma-za-1-den.html).

Aplikace ve spolupráci s notářem umožňuje již dnes výraznou úsporu času a peněz v průběhu celého procesu a ilustruje tak možnosti zjednodušení a digitalizace zakládání společností s ručením omezeným.

Kód zveřejňujeme pod [MIT Licencí](licence.md).

Vytvořeno za pomoci [Nette Frameworku](http://nette.org).

## Návod na spuštění

### Předpoklady

- Nainstalovaný [Docker](https://www.docker.com/get-started)
- Nainstalovaný [Git](https://git-scm.com/downloads)

### Spuštění

- Naclonujte repozitář do svého počítače (`git clone git@github.com:pirati-web/urad.pirati.cz.git`)
- Otevřete terminál/příkazovou řádku a přejděte do adresáře právě naklonovaného repozitáře
- Zkopírujte `config/config.local.neon.template` pod novým názvem `config/config.local.neon`
- Spusťte aplikaci v terminálu (`docker-compose up --build`)
- [Otevřete aplikaci v prohlížeči](http://localhost:8000)


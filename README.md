# RM-Net - DOV Control Panel
<p align="center">
  <img src="https://avatars.githubusercontent.com/u/116940927" alt="RM-Net - DOV oPOS" width="200" height="200">
</p>

## Funkcije
- Upravljajte več strežnikov z ene nadzorne plošče
- Enostrežniške, večstrežniške in zrcaljene gruče.
- Upravljanje spletnega strežnika
- Upravljanje poštnega strežnika
- Upravljanje DNS strežnika
- Virtualizacija (OpenVZ)
- Prijava skrbnika, prodajalca, odjemalca in uporabnika e-pošte
- Odprtokodna programska oprema ([BSD license](LICENSE))

## Podprti demoni
- HTTP: Apache2 in NGINX
- Statistika HTTP: Webalizer, GoAccess in AWStats
- Let's Encrypt: Acme.sh in certbot
- SMTP: Postfix
- POP3/IMAP: Dovecot
- Filter neželene pošte: Rspamd in Amavis
- FTP: PureFTPD
- DNS: BIND9 in PowerDNS[^1]
- Baza podatkov: MariaDB in MySQL

[^1]: ni aktivno testirano

## Podprti operacijski sistemi
- Debian 9 - 11 in testiranje
- Ubuntu 16.04 - 20.04
- CentOS 7 in 8

## Skript za samodejno namestitev
"Perfect Server" lahko namestite z RM-Net - DOV z uporabo [naš uradni samodejni namestitveni program](https://)

## Orodje za selitev
Orodje za selitev vam pomaga uvoziti podatke iz drugih nadzornih plošč (trenutno RM-Net - DOV CP 2 in 3 – 3.2, Plesk 10 – 12.5, Plesk Onyx, CPanel[^2] in Confixx 3). Za več informacij glejte [TUKAJ](https://github.com/RMNetDOV/RMNetDOV3/wiki/Kako-preseliti-RM-Net---DOV-CP-2,-RM-Net---DOV-CP-3.x,-Confixx-ali-Plesk-na-RM-Net---DOV-CP-3.2-(en-stre%C5%BEnik))
[^2]: Migration Toolkit zdaj vsebuje beta podporo za selitev CPanel v RM-Net - DOV CP.

## Prispevam
Če želite prispevati k razvoju RM-Net - DOV CP, preberite smernice za prispevanje: [CONTRIBUTING.MD](CONTRIBUTING.md)


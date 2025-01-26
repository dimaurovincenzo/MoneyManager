# My VDM - Gestore Finanziario Personale

## 📝 Descrizione
My VDM è un'applicazione web per la gestione delle finanze personali, progettata per offrire un controllo completo su entrate, uscite e pagamenti ricorrenti. L'applicazione è ottimizzata per l'hosting su Altervista e include funzionalità avanzate per la gestione automatica delle transazioni ricorrenti.

## 🚀 Funzionalità Principali

### Dashboard
- 📊 Panoramica del saldo totale
- 📈 Statistiche mensili di entrate e uscite
- 💳 Visualizzazione rapida di tutti i conti
- 📋 Lista degli ultimi movimenti
- 📊 Top categorie di spesa del mese

### Gestione Finanziaria
- 💰 Gestione multi-conto
- 🏷️ Categorizzazione delle transazioni
- 🔄 Sistema di pagamenti ricorrenti automatici
- 📱 Interfaccia responsive e mobile-friendly

### Sistema di Pagamenti Ricorrenti
- ⚡ Esecuzione automatica dei pagamenti
- 📅 Pianificazione mensile
- 🔄 Gestione dello stato attivo/inattivo
- 📊 Monitoraggio delle esecuzioni

## 🛠️ Requisiti Tecnici

### Server
- PHP 7.4 o superiore
- MySQL 5.7 o superiore
- Supporto per mysqli
- Supporto per sessioni PHP

### Client
- Browser web moderno con supporto JavaScript
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0

## 📦 Struttura del Database

### Tabelle Principali
- `accounts`: Gestione dei conti
- `categories`: Categorie per transazioni
- `transactions`: Registro delle transazioni
- `recurring_payments`: Configurazione pagamenti ricorrenti
- `users`: Gestione utenti
- `auth_tokens`: Gestione sessioni persistenti

## 🔧 Installazione

1. **Configurazione Database**
   ```sql
   CREATE DATABASE my_vdm;
   USE my_vdm;
   ```

2. **Configurazione Config File**
   - Modificare `includes/config.php` con i parametri del database:
   ```php
   $db_host = 'localhost';
   $db_user = 'your_username';
   $db_pass = 'your_password';
   $db_name = 'my_vdm';
   ```

3. **Permessi File**
   ```bash
   chmod 755 execute_all_crons.php
   chmod 644 includes/config.php
   ```

## 🔒 Sicurezza

- 🔐 Autenticazione utente obbligatoria
- 🔑 Sistema di token per sessioni persistenti
- 🛡️ Protezione contro SQL injection
- 🔒 Sanitizzazione input utente
- 🔐 Password hashate nel database

## 📱 Interfaccia Utente

### Componenti Principali
- Navbar inferiore per navigazione mobile
- Griglie responsive per conti e categorie
- Modal per inserimento/modifica dati
- Indicatori visivi per trend finanziari

### Temi e Stili
- Sistema di colori per categorie
- Icone Bootstrap per migliore UX
- Layout responsive e adattivo
- Supporto tema chiaro/scuro

## 🔄 Sistema Cron

### Esecuzione Automatica
- File `execute_all_crons.php` per gestione pagamenti
- Esecuzione giornaliera automatica
- Log dettagliati delle operazioni
- Gestione errori e rollback

### Monitoraggio
- Interfaccia web per controllo esecuzioni
- Storico delle operazioni
- Notifiche di errori
- Statistiche di esecuzione

## 👥 Gestione Utenti

### Funzionalità
- Registrazione nuovo utente
- Login con remember me
- Cambio password
- Gestione sessioni

### Permessi
- Controllo accessi
- Protezione delle API
- Validazione token
- Logout automatico

## 📈 API Endpoints

### Transazioni
- `api/get_recent_transactions.php`
- `api/get_monthly_stats.php`
- `api/get_total_balance.php`

### Gestione Conti
- `api/get_accounts.php`
- `api/save_account.php`

### Pagamenti Ricorrenti
- `api/get_recurring_payments.php`
- `api/save_recurring_payment.php`
- `api/toggle_recurring_payment.php`

3. **Primo accesso**
- `set_password.php`

## 🤝 Contribuire

1. Fork del repository
2. Crea un branch per la feature (`git checkout -b feature/AmazingFeature`)
3. Commit delle modifiche (`git commit -m 'Add some AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Apri una Pull Request

## 📄 Licenza
Distribuito sotto licenza MIT. Vedere `LICENSE` per maggiori informazioni.


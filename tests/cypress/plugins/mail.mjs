// L'URL di Mailpit dipende dall'ambiente (CI o locale)
const MAILPIT_URL = process.env.CI ? 'http://mailpit:8025' : 'http://127.0.0.1:8025';

/**
 * Recupera le mail da Mailpit con un meccanismo di retry (3 secondi)
 * Usa la fetch nativa di Node.js
 */
async function getMails() {
  for (let i = 0; i < 3; i += 1) {
    try {
      const response = await fetch(`${MAILPIT_URL}/api/v1/messages`);
      
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      
      const data = await response.json();
      
      if (data.messages && data.messages.length > 0) {
        // Recuperiamo il dettaglio di ogni mail per avere il corpo del messaggio
        const detailedMails = await Promise.all(
          data.messages.map(async (msg) => {
            const detailRes = await fetch(`${MAILPIT_URL}/api/v1/message/${msg.ID}`);
            const detail = await detailRes.json();
            
            return {
              id: msg.ID,
              headers: {
                subject: detail.Subject,
                from: `${detail.From.Name} <${detail.From.Address}>`,
                to: msg.To[0].Address
              },
              body: detail.Text,
              html: detail.HTML
            };
          })
        );
        return detailedMails;
      }
    } catch (error) {
      console.error('Errore Mailpit:', error.message);
    }

    // Aspetta 1 secondo prima del prossimo tentativo
    await new Promise((r) => setTimeout(r, 1000));
  }

  return [];
}

/**
 * Cancella tutte le email presenti in Mailpit
 */
async function clearEmails() {
  try {
    await fetch(`${MAILPIT_URL}/api/v1/messages`, {
      method: 'DELETE'
    });
  } catch (error) {
    console.error('Errore durante la cancellazione:', error.message);
  }
  return null;
}

/**
 * Non serve più avviare un server SMTP manuale con Mailpit
 */
function startMailServer() {
  console.log('Mailpit è gestito via Docker.');
  return null;
}

export { getMails, clearEmails, startMailServer };

import { useEffect, useRef } from 'react';
import { MERCURE_URL } from '../api/client';

/**
 * S'abonne au topic Mercure d'une session et appelle onEvent(data) à chaque
 * message reçu (question démarrée, terminée, classement mis à jour...).
 */
export function useMercure(sessionId, onEvent) {
  const onEventRef = useRef(onEvent);
  onEventRef.current = onEvent;

  useEffect(() => {
    if (!sessionId) return undefined;

    const url = new URL(MERCURE_URL);
    url.searchParams.append('topic', `session/${sessionId}`);

    const source = new EventSource(url);
    source.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        onEventRef.current?.(data);
      } catch (e) {
        console.error('Message Mercure invalide', e);
      }
    };

    return () => source.close();
  }, [sessionId]);
}

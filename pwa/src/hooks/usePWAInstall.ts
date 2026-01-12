import { useState, useEffect } from 'react';

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

const INSTALL_PROMPT_DISMISSED_KEY = 'pwa_install_dismissed';
const INSTALL_PROMPT_COUNT_KEY = 'pwa_install_prompt_count';
const MAX_PROMPT_COUNT = 5; // Show prompt up to 5 times
const PROMPT_COOLDOWN_DAYS = 3; // Wait 3 days between prompts

export function usePWAInstall() {
  const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [isInstalled, setIsInstalled] = useState(false);
  const [isInstallable, setIsInstallable] = useState(false);

  useEffect(() => {
    // Check if app is already installed
    const checkInstalled = () => {
      // Check if running as standalone (installed PWA)
      if (window.matchMedia('(display-mode: standalone)').matches) {
        setIsInstalled(true);
        return true;
      }
      
      // Check if running from home screen on iOS
      if ((window.navigator as any).standalone === true) {
        setIsInstalled(true);
        return true;
      }

      return false;
    };

    if (checkInstalled()) {
      return;
    }

    // Listen for beforeinstallprompt event
    const handleBeforeInstallPrompt = (e: Event) => {
      e.preventDefault();
      const promptEvent = e as BeforeInstallPromptEvent;
      setDeferredPrompt(promptEvent);
      
      // Check if we should show the prompt
      const shouldShow = shouldShowInstallPrompt();
      setIsInstallable(shouldShow);
    };

    // Listen for app installed event
    const handleAppInstalled = () => {
      setIsInstalled(true);
      setIsInstallable(false);
      setDeferredPrompt(null);
      // Clear prompt tracking since it's installed
      localStorage.removeItem(INSTALL_PROMPT_DISMISSED_KEY);
      localStorage.removeItem(INSTALL_PROMPT_COUNT_KEY);
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);

    // Check if already installed on mount
    checkInstalled();

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('appinstalled', handleAppInstalled);
    };
  }, []);

  const shouldShowInstallPrompt = (): boolean => {
    // Don't show if already installed
    if (isInstalled) return false;

    // Check if user permanently dismissed
    const dismissed = localStorage.getItem(INSTALL_PROMPT_DISMISSED_KEY);
    if (dismissed === 'permanent') return false;

    // Check prompt count
    const countStr = localStorage.getItem(INSTALL_PROMPT_COUNT_KEY);
    const count = countStr ? parseInt(countStr, 10) : 0;
    if (count >= MAX_PROMPT_COUNT) {
      // Mark as permanently dismissed after max attempts
      localStorage.setItem(INSTALL_PROMPT_DISMISSED_KEY, 'permanent');
      return false;
    }

    // Check cooldown period
    const lastPromptStr = localStorage.getItem('pwa_install_last_prompt');
    if (lastPromptStr) {
      const lastPrompt = new Date(lastPromptStr);
      const daysSince = (Date.now() - lastPrompt.getTime()) / (1000 * 60 * 60 * 24);
      if (daysSince < PROMPT_COOLDOWN_DAYS) {
        return false;
      }
    }

    return true;
  };

  const promptInstall = async (): Promise<void> => {
    if (!deferredPrompt) {
      return;
    }

    try {
      // Show the install prompt
      await deferredPrompt.prompt();
      
      // Wait for user response
      const { outcome } = await deferredPrompt.userChoice;
      
      // Update tracking
      const countStr = localStorage.getItem(INSTALL_PROMPT_COUNT_KEY);
      const count = countStr ? parseInt(countStr, 10) : 0;
      localStorage.setItem(INSTALL_PROMPT_COUNT_KEY, (count + 1).toString());
      localStorage.setItem('pwa_install_last_prompt', new Date().toISOString());

      if (outcome === 'accepted') {
        // User accepted - will be handled by appinstalled event
        setIsInstalled(true);
      } else {
        // User dismissed - check if we should stop prompting
        if (count + 1 >= MAX_PROMPT_COUNT) {
          localStorage.setItem(INSTALL_PROMPT_DISMISSED_KEY, 'permanent');
          setIsInstallable(false);
        }
      }

      // Clear the deferred prompt
      setDeferredPrompt(null);
    } catch (error) {
      console.error('Error showing install prompt:', error);
    }
  };

  return {
    isInstallable,
    isInstalled,
    promptInstall,
  };
}

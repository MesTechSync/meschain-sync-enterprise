/**
 * Advanced Animation Provider with Micro-interactions
 * Handles page transitions, element animations, loading states, and micro-interactions
 */

import React, { createContext, useContext, ReactNode, useEffect, useState } from 'react';
import { AnimatePresence, motion, useAnimationControls, useInView } from 'framer-motion';
import { useSpring, animated, useTransition, config } from '@react-spring/web';

// Types
interface AnimationConfig {
  enabled: boolean;
  reducedMotion: boolean;
  duration: 'fast' | 'normal' | 'slow';
  easing: 'ease' | 'ease-in' | 'ease-out' | 'ease-in-out' | 'bounce' | 'elastic';
  stagger: number;
  parallax: boolean;
  pageTransitions: boolean;
  microInteractions: boolean;
  loadingAnimations: boolean;
  scrollAnimations: boolean;
}

interface AnimationContextType {
  config: AnimationConfig;
  updateConfig: (newConfig: Partial<AnimationConfig>) => void;
  presets: AnimationPreset[];
  applyPreset: (presetId: string) => void;
}

interface AnimationPreset {
  id: string;
  name: string;
  description: string;
  config: AnimationConfig;
}

// Animation variants
export const pageVariants = {
  initial: { opacity: 0, x: -20 },
  in: { opacity: 1, x: 0 },
  out: { opacity: 0, x: 20 }
};

export const cardVariants = {
  hidden: { opacity: 0, y: 20, scale: 0.95 },
  visible: { 
    opacity: 1, 
    y: 0, 
    scale: 1,
    transition: {
      duration: 0.3,
      ease: "easeOut"
    }
  },
  hover: {
    y: -4,
    scale: 1.02,
    boxShadow: "0px 10px 30px rgba(0, 0, 0, 0.15)",
    transition: {
      duration: 0.2,
      ease: "easeOut"
    }
  },
  tap: {
    scale: 0.98,
    transition: {
      duration: 0.1
    }
  }
};

export const listVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.2
    }
  }
};

export const itemVariants = {
  hidden: { opacity: 0, x: -20 },
  visible: {
    opacity: 1,
    x: 0,
    transition: {
      duration: 0.3,
      ease: "easeOut"
    }
  }
};

export const modalVariants = {
  hidden: {
    opacity: 0,
    scale: 0.8,
    transition: {
      duration: 0.2
    }
  },
  visible: {
    opacity: 1,
    scale: 1,
    transition: {
      duration: 0.3,
      ease: "easeOut"
    }
  },
  exit: {
    opacity: 0,
    scale: 0.8,
    transition: {
      duration: 0.2
    }
  }
};

export const slideVariants = {
  enter: (direction: number) => ({
    x: direction > 0 ? 1000 : -1000,
    opacity: 0
  }),
  center: {
    zIndex: 1,
    x: 0,
    opacity: 1
  },
  exit: (direction: number) => ({
    zIndex: 0,
    x: direction < 0 ? 1000 : -1000,
    opacity: 0
  })
};

// Default configuration
const defaultConfig: AnimationConfig = {
  enabled: true,
  reducedMotion: false,
  duration: 'normal',
  easing: 'ease-out',
  stagger: 0.1,
  parallax: true,
  pageTransitions: true,
  microInteractions: true,
  loadingAnimations: true,
  scrollAnimations: true
};

// Animation presets
const animationPresets: AnimationPreset[] = [
  {
    id: 'minimal',
    name: 'Minimal',
    description: 'Subtle animations for professional interfaces',
    config: {
      ...defaultConfig,
      duration: 'fast',
      easing: 'ease',
      microInteractions: false,
      parallax: false
    }
  },
  {
    id: 'smooth',
    name: 'Smooth',
    description: 'Balanced animations for general use',
    config: {
      ...defaultConfig,
      duration: 'normal',
      easing: 'ease-out'
    }
  },
  {
    id: 'dynamic',
    name: 'Dynamic',
    description: 'Rich animations with bounce and elasticity',
    config: {
      ...defaultConfig,
      duration: 'normal',
      easing: 'bounce',
      stagger: 0.15,
      microInteractions: true
    }
  },
  {
    id: 'accessible',
    name: 'Accessible',
    description: 'Reduced motion for accessibility',
    config: {
      ...defaultConfig,
      enabled: true,
      reducedMotion: true,
      duration: 'fast',
      pageTransitions: false,
      microInteractions: false,
      parallax: false
    }
  }
];

// Animation context
const AnimationContext = createContext<AnimationContextType | undefined>(undefined);

export const useAnimation = () => {
  const context = useContext(AnimationContext);
  if (!context) {
    throw new Error('useAnimation must be used within AnimationProvider');
  }
  return context;
};

// Animation provider component
interface AnimationProviderProps {
  children: ReactNode;
  persistConfig?: boolean;
}

export const AnimationProvider: React.FC<AnimationProviderProps> = ({
  children,
  persistConfig = true
}) => {
  const [config, setConfig] = useState<AnimationConfig>(defaultConfig);

  // Load config from localStorage
  useEffect(() => {
    if (persistConfig) {
      const savedConfig = localStorage.getItem('meschain_animation_config');
      if (savedConfig) {
        try {
          const parsedConfig = JSON.parse(savedConfig);
          setConfig({ ...defaultConfig, ...parsedConfig });
        } catch (error) {
          console.warn('Failed to parse animation config:', error);
        }
      }
    }

    // Detect reduced motion preference
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (mediaQuery.matches) {
      setConfig(prev => ({ ...prev, reducedMotion: true }));
    }

    const handleChange = (e: MediaQueryListEvent) => {
      setConfig(prev => ({ ...prev, reducedMotion: e.matches }));
    };

    mediaQuery.addEventListener('change', handleChange);
    return () => mediaQuery.removeEventListener('change', handleChange);
  }, [persistConfig]);

  // Save config to localStorage
  useEffect(() => {
    if (persistConfig) {
      localStorage.setItem('meschain_animation_config', JSON.stringify(config));
    }
  }, [config, persistConfig]);

  const updateConfig = (newConfig: Partial<AnimationConfig>) => {
    setConfig(prev => ({ ...prev, ...newConfig }));
  };

  const applyPreset = (presetId: string) => {
    const preset = animationPresets.find(p => p.id === presetId);
    if (preset) {
      setConfig(preset.config);
    }
  };

  const contextValue: AnimationContextType = {
    config,
    updateConfig,
    presets: animationPresets,
    applyPreset
  };

  return (
    <AnimationContext.Provider value={contextValue}>
      {children}
    </AnimationContext.Provider>
  );
};

// Custom animation hooks

// Fade in animation hook
export const useFadeIn = (delay = 0) => {
  const { config: animConfig } = useAnimation();
  
  const springConfig = animConfig.reducedMotion 
    ? { duration: 0 } 
    : animConfig.duration === 'fast' 
      ? config.wobbly 
      : animConfig.duration === 'slow' 
        ? config.slow 
        : config.default;
  
  return useSpring({
    from: { opacity: 0, transform: 'translateY(20px)' },
    to: { opacity: 1, transform: 'translateY(0px)' },
    delay,
    config: springConfig
  });
};

// Slide in animation hook
export const useSlideIn = (direction: 'left' | 'right' | 'up' | 'down' = 'left', delay = 0) => {
  const { config: animConfig } = useAnimation();
  
  const springConfig = animConfig.reducedMotion 
    ? { duration: 0 } 
    : animConfig.duration === 'fast' 
      ? config.wobbly 
      : animConfig.duration === 'slow' 
        ? config.slow 
        : config.default;
  
  const getInitialTransform = () => {
    switch (direction) {
      case 'left': return 'translateX(-50px)';
      case 'right': return 'translateX(50px)';
      case 'up': return 'translateY(-50px)';
      case 'down': return 'translateY(50px)';
      default: return 'translateX(-50px)';
    }
  };

  return useSpring({
    from: { opacity: 0, transform: getInitialTransform() },
    to: { opacity: 1, transform: 'translate(0px, 0px)' },
    delay,
    config: springConfig
  });
};

// Scale animation hook
export const useScale = (initialScale = 0.8, delay = 0) => {
  const { config: animConfig } = useAnimation();
  
  const springConfig = animConfig.reducedMotion 
    ? { duration: 0 } 
    : animConfig.duration === 'fast' 
      ? config.wobbly 
      : animConfig.duration === 'slow' 
        ? config.slow 
        : config.default;
  
  return useSpring({
    from: { opacity: 0, transform: `scale(${initialScale})` },
    to: { opacity: 1, transform: 'scale(1)' },
    delay,
    config: springConfig
  });
};

// Stagger animation hook
export const useStagger = (items: any[], delay = 100) => {
  const { config: animConfig } = useAnimation();
  
  const springConfig = animConfig.reducedMotion 
    ? { duration: 0 } 
    : animConfig.duration === 'fast' 
      ? config.wobbly 
      : animConfig.duration === 'slow' 
        ? config.slow 
        : config.default;
  
  return useTransition(items, {
    from: { opacity: 0, transform: 'translateY(20px)' },
    enter: { opacity: 1, transform: 'translateY(0px)' },
    trail: animConfig.reducedMotion ? 0 : delay,
    config: springConfig
  });
};

// Scroll-triggered animation hook
export const useScrollAnimation = (threshold = 0.1) => {
  const { config: animConfig } = useAnimation();
  const controls = useAnimationControls();
  const ref = React.useRef(null);
  const inView = useInView(ref, { amount: threshold });

  useEffect(() => {
    if (inView && animConfig.scrollAnimations && !animConfig.reducedMotion) {
      controls.start('visible');
    }
  }, [controls, inView, animConfig.scrollAnimations, animConfig.reducedMotion]);

  return { ref, controls, inView };
};

// Hover animation hook
export const useHoverAnimation = () => {
  const { config: animConfig } = useAnimation();
  
  const [springs, api] = useSpring(() => ({
    scale: 1,
    shadow: 0,
    config: animConfig.reducedMotion ? { duration: 0 } : config.default
  }));

  const handleMouseEnter = () => {
    if (animConfig.microInteractions && !animConfig.reducedMotion) {
      api.start({ scale: 1.05, shadow: 15 });
    }
  };

  const handleMouseLeave = () => {
    if (animConfig.microInteractions && !animConfig.reducedMotion) {
      api.start({ scale: 1, shadow: 0 });
    }
  };

  return { springs, handleMouseEnter, handleMouseLeave };
};

// Loading animation hook
export const useLoadingAnimation = () => {
  const { config } = useAnimation();
  
  const [springs] = useSpring(() => ({
    from: { rotate: 0 },
    to: async (next) => {
      while (config.loadingAnimations && !config.reducedMotion) {
        await next({ rotate: 360 });
        await next({ rotate: 0 });
      }
    },
    config: { duration: 1000 },
    reset: true,
    loop: config.loadingAnimations && !config.reducedMotion
  }));

  return springs;
};

// Page transition hook
export const usePageTransition = () => {
  const { config } = useAnimation();
  
  return {
    initial: config.pageTransitions && !config.reducedMotion ? pageVariants.initial : {},
    animate: config.pageTransitions && !config.reducedMotion ? pageVariants.in : {},
    exit: config.pageTransitions && !config.reducedMotion ? pageVariants.out : {},
    transition: {
      duration: config.duration === 'fast' ? 0.2 : config.duration === 'slow' ? 0.5 : 0.3,
      ease: config.easing
    }
  };
};

// Animated components
export const AnimatedCard: React.FC<{ children: ReactNode; className?: string; onClick?: () => void }> = ({
  children,
  className,
  onClick
}) => {
  const { config } = useAnimation();
  const { ref, controls } = useScrollAnimation();

  return (
    <motion.div
      ref={ref}
      animate={controls}
      initial="hidden"
      variants={config.enabled && !config.reducedMotion ? cardVariants : {}}
      whileHover={config.microInteractions && !config.reducedMotion ? "hover" : undefined}
      whileTap={config.microInteractions && !config.reducedMotion ? "tap" : undefined}
      className={className}
      onClick={onClick}
      style={{ cursor: onClick ? 'pointer' : 'default' }}
    >
      {children}
    </motion.div>
  );
};

export const AnimatedList: React.FC<{ children: ReactNode; className?: string }> = ({
  children,
  className
}) => {
  const { config } = useAnimation();
  const { ref, controls } = useScrollAnimation();

  return (
    <motion.div
      ref={ref}
      animate={controls}
      initial="hidden"
      variants={config.enabled && !config.reducedMotion ? listVariants : {}}
      className={className}
    >
      {children}
    </motion.div>
  );
};

export const AnimatedListItem: React.FC<{ children: ReactNode; className?: string }> = ({
  children,
  className
}) => {
  const { config } = useAnimation();

  return (
    <motion.div
      variants={config.enabled && !config.reducedMotion ? itemVariants : {}}
      className={className}
    >
      {children}
    </motion.div>
  );
};

export const AnimatedModal: React.FC<{ 
  children: ReactNode; 
  isOpen: boolean; 
  onClose?: () => void;
  className?: string;
}> = ({ children, isOpen, onClose, className }) => {
  const { config } = useAnimation();

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          {/* Backdrop */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
            style={{
              position: 'fixed',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              backgroundColor: 'rgba(0, 0, 0, 0.5)',
              zIndex: 1000,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          />
          
          {/* Modal content */}
          <motion.div
            variants={config.enabled && !config.reducedMotion ? modalVariants : {}}
            initial="hidden"
            animate="visible"
            exit="exit"
            className={className}
            style={{
              position: 'fixed',
              top: '50%',
              left: '50%',
              transform: 'translate(-50%, -50%)',
              zIndex: 1001
            }}
          >
            {children}
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
};

export const AnimatedCounter: React.FC<{ 
  value: number; 
  duration?: number;
  className?: string;
}> = ({ value, duration = 1000, className }) => {
  const { config } = useAnimation();
  
  const { number } = useSpring({
    from: { number: 0 },
    number: value,
    delay: 200,
    config: config.reducedMotion ? { duration: 0 } : { duration }
  });

  return (
    <animated.span className={className}>
      {number.to((n) => Math.floor(n).toLocaleString())}
    </animated.span>
  );
};

export const AnimatedProgressBar: React.FC<{
  progress: number;
  height?: number;
  color?: string;
  className?: string;
}> = ({ progress, height = 4, color = '#1976d2', className }) => {
  const { config: animConfig } = useAnimation();
  
  const { width } = useSpring({
    from: { width: '0%' },
    to: { width: `${Math.min(100, Math.max(0, progress))}%` },
    config: animConfig.reducedMotion ? { duration: 0 } : config.default
  });

  return (
    <div 
      className={className}
      style={{
        width: '100%',
        height,
        backgroundColor: 'rgba(0, 0, 0, 0.1)',
        borderRadius: height / 2,
        overflow: 'hidden'
      }}
    >
      <animated.div
        style={{
          height: '100%',
          backgroundColor: color,
          borderRadius: height / 2,
          width
        }}
      />
    </div>
  );
};

// Parallax component
export const ParallaxContainer: React.FC<{
  children: ReactNode;
  speed?: number;
  className?: string;
}> = ({ children, speed = 0.5, className }) => {
  const { config } = useAnimation();
  const [offsetY, setOffsetY] = useState(0);

  useEffect(() => {
    if (!config.parallax || config.reducedMotion) return;

    const handleScroll = () => {
      setOffsetY(window.pageYOffset * speed);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [speed, config.parallax, config.reducedMotion]);

  return (
    <div 
      className={className}
      style={{
        transform: config.parallax && !config.reducedMotion 
          ? `translateY(${offsetY}px)` 
          : 'none'
      }}
    >
      {children}
    </div>
  );
};

export default AnimationProvider; 
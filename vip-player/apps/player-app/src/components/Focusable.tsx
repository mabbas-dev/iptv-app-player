import React, { useState } from 'react';
import { Pressable, PressableProps, StyleProp, ViewStyle } from 'react-native';
import { colors } from '../lib/theme';

interface FocusableProps extends PressableProps {
  style?: StyleProp<ViewStyle>;
  focusedStyle?: StyleProp<ViewStyle>;
  children: React.ReactNode;
}

/**
 * Pressable with a visible focus state, so the app is usable
 * with an Android TV remote (D-pad) as well as touch.
 */
export function Focusable({ style, focusedStyle, children, ...props }: FocusableProps) {
  const [focused, setFocused] = useState(false);

  return (
    <Pressable
      {...props}
      onFocus={(e) => {
        setFocused(true);
        props.onFocus?.(e);
      }}
      onBlur={(e) => {
        setFocused(false);
        props.onBlur?.(e);
      }}
      style={({ pressed }) => [
        style,
        focused && {
          borderColor: colors.gold,
          borderWidth: 2,
          transform: [{ scale: 1.04 }],
        },
        focused && focusedStyle,
        pressed && { opacity: 0.8 },
      ]}
    >
      {children}
    </Pressable>
  );
}

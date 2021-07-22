import { Button, ButtonProps as AntButtonProps } from "antd";
import { SizeType } from "antd/lib/config-provider/SizeContext";
import classnames from "classnames";
import React from "react";
import styles from "./style.module.css";

export interface ButtonProps extends AntButtonProps {
	text?: string | React.ReactNode;
	isSecondary?: boolean;
	prefixIcon?: any;
	block?: boolean;
	size?: SizeType;
	round?: boolean;
	style?: object;
	ghost?: boolean;
	iconRight?: boolean;
	className?: string;
	type?: "text" | "link" | "ghost" | "default" | "primary" | "dashed";
}

export const CustomButton: React.FC<ButtonProps> = ({
	text = " ",
	isSecondary = false,
	prefixIcon,
	block = false,
	round = false,
	size = "middle",
	type = "primary",
	iconRight = false,
	className = "",
	style = {},
	...props
}) => {
	const colorStyle = isSecondary ? "#209AE5" : "white";
	const bgStyle = isSecondary ? "white" : "#209AE5";
	const iconRightStyle: object = {
		display: iconRight ? "inline-flex" : undefined,
		alignItems: iconRight ? "center" : undefined,
		justifyContent: iconRight ? "center" : undefined,
		flexDirection: iconRight ? "row-reverse" : undefined,
	};
	return (
		<Button
			className={classnames(styles.button, className)}
			block={block}
			type={isSecondary ? "default" : type}
			icon={prefixIcon}
			style={{
				color: colorStyle,
				backgroundColor: bgStyle,
				height: size === "middle" || size === "large" ? "36px" : "",
				borderRadius: round ? "8px" : "",
				...style,
				...iconRightStyle,
				textTransform: isSecondary ? "none" : "uppercase",
			}}
			size={size}
			// eslint-disable-next-line react/jsx-props-no-spreading
			{...props}
		>
			{text}
		</Button>
	);
};

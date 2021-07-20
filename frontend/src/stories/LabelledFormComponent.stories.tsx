/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/jsx-props-no-spreading */
import React from "react";
import "../App.less";
import { Story, Meta } from "@storybook/react";
import { CustomInput } from "../components/CustomInput";

import {
	LabelledFormComponent,
	LabelledFormComponentProps,
} from "../components/LabelledFormComponent";

export default {
	title: "Shared/LabelledFormComponent",
	component: LabelledFormComponent,
} as Meta;

const Template: Story<LabelledFormComponentProps> = (args) => (
	<LabelledFormComponent {...args} />
);

export const WithInput = Template.bind({});
WithInput.args = {
	labelText: "Email",
	width: "250px",
	formComponent: (
		<CustomInput
			placeholder="Eg. example@gmail.com"
			backgroundColor="rgba(0,0,0,0)"
		/>
	),
};

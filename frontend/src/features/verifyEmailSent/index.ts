import { createSlice } from "@reduxjs/toolkit";
import { RootState } from "../../app/store";

export interface VerifyState {
	verifyAgain: boolean;
}

const initialState: VerifyState = {
	verifyAgain: false,
};

export const verifyEmailSlice = createSlice({
	name: "verifyEmail",
	initialState,
	reducers: {
		sendVerify: (state) => {
			state.verifyAgain = true;
		},
	},
});

export const { sendVerify } = verifyEmailSlice.actions;

export const selectVerifySent = (state: RootState) =>
	state.verifyEmail.verifyAgain;

export default verifyEmailSlice.reducer;
